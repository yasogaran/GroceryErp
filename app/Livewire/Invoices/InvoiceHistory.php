<?php

namespace App\Livewire\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class InvoiceHistory extends Component
{
    use WithPagination;

    // Filters
    public $dateFrom = '';
    public $dateTo = '';
    public $searchTerm = ''; // For customer name, mobile, invoice number
    public $filterStatus = '';
    public $filterCashier = '';

    // Selected invoice for detail view
    public $selectedInvoiceId = null;
    public $selectedInvoice = null;

    // Payment modal
    public $showPaymentModal = false;
    public $paymentAmount = 0;
    public $paymentMode = 'cash';
    public $paymentBankAccount = null;

    protected $queryString = [
        'dateFrom',
        'dateTo',
        'searchTerm',
        'filterStatus',
        'filterCashier',
    ];

    public function mount()
    {
        // Default to current month
        if (!$this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterCashier()
    {
        $this->resetPage();
    }

    public function selectInvoice($invoiceId)
    {
        $this->selectedInvoiceId = $invoiceId;
        $this->loadSelectedInvoice();
    }

    public function loadSelectedInvoice()
    {
        if ($this->selectedInvoiceId) {
            $this->selectedInvoice = Sale::with([
                'items.product',
                'customer',
                'cashier',
                'payments.bankAccount',
                'shift'
            ])->find($this->selectedInvoiceId);
        }
    }

    public function openPaymentModal()
    {
        if (!$this->selectedInvoice) {
            session()->flash('error', 'Please select an invoice first');
            return;
        }

        $totalPaid = $this->selectedInvoice->payments->sum('amount');
        $remainingBalance = $this->selectedInvoice->total_amount - $totalPaid;

        if ($remainingBalance <= 0) {
            session()->flash('error', 'This invoice is already fully paid');
            return;
        }

        $this->paymentAmount = $remainingBalance;
        $this->paymentMode = 'cash';
        $this->paymentBankAccount = null;
        $this->showPaymentModal = true;
    }

    public function recordPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMode' => 'required|in:cash,bank_transfer',
            'paymentBankAccount' => 'required_if:paymentMode,bank_transfer',
        ], [
            'paymentAmount.required' => 'Please enter payment amount',
            'paymentAmount.min' => 'Amount must be greater than 0',
            'paymentBankAccount.required_if' => 'Please select a bank account',
        ]);

        try {
            DB::transaction(function () {
                $sale = Sale::findOrFail($this->selectedInvoiceId);

                // Calculate remaining balance
                $totalPaid = $sale->payments->sum('amount');
                $remainingBalance = $sale->total_amount - $totalPaid;

                // Validate payment amount doesn't exceed balance
                if ($this->paymentAmount > $remainingBalance) {
                    throw new \Exception('Payment amount cannot exceed remaining balance of Rs. ' . number_format($remainingBalance, 2));
                }

                // Create payment record
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_mode' => $this->paymentMode,
                    'bank_account_id' => $this->paymentMode === 'bank_transfer' ? $this->paymentBankAccount : null,
                    'amount' => $this->paymentAmount,
                ]);

                // Update sale payment status
                $newTotalPaid = $totalPaid + $this->paymentAmount;
                if ($newTotalPaid >= $sale->total_amount) {
                    $sale->update(['payment_status' => 'paid']);
                } elseif ($newTotalPaid > 0) {
                    $sale->update(['payment_status' => 'partial']);
                }

                // Update customer total purchases
                if ($sale->customer_id) {
                    $sale->customer->increment('total_purchases', $this->paymentAmount);
                }

                // Update shift totals if payment is for current shift
                if (auth()->user()->currentShift && $sale->shift_id === auth()->user()->currentShift->id) {
                    $shift = auth()->user()->currentShift;
                    $shift->increment('total_sales', $this->paymentAmount);

                    if ($this->paymentMode === 'cash') {
                        $shift->increment('total_cash_sales', $this->paymentAmount);
                    } else {
                        $shift->increment('total_bank_sales', $this->paymentAmount);
                    }
                }

                $this->showPaymentModal = false;
                $this->loadSelectedInvoice();

                session()->flash('success', 'Payment recorded successfully! Amount: Rs. ' . number_format($this->paymentAmount, 2));
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['dateFrom', 'dateTo', 'searchTerm', 'filterStatus', 'filterCashier']);
        $this->mount();
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Sale::with(['customer', 'cashier', 'payments'])
            ->orderBy('sale_date', 'desc');

        // Apply filters
        if ($this->dateFrom) {
            $query->whereDate('sale_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('sale_date', '<=', $this->dateTo);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', '%' . $this->searchTerm . '%')
                            ->orWhere('phone', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('payment_status', $this->filterStatus);
        }

        if ($this->filterCashier) {
            $query->where('created_by', $this->filterCashier);
        }

        $invoices = $query->paginate(15);

        // Get all cashiers for filter
        $cashiers = User::whereIn('role', ['cashier', 'manager', 'admin'])
            ->orderBy('name')
            ->get();

        // Get bank accounts for payment
        $bankAccounts = Account::where('account_type', 'asset')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return view('livewire.invoices.invoice-history', [
            'invoices' => $invoices,
            'cashiers' => $cashiers,
            'bankAccounts' => $bankAccounts,
        ]);
    }
}
