<?php

namespace App\Livewire\Suppliers\Payments;

use App\Models\Account;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\PaymentAllocationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RecordPayment extends Component
{
    public $supplier_id = '';
    public $payment_date;
    public $amount = '';
    public $payment_mode = 'cash';
    public $bank_account_id = '';
    public $bank_reference = '';
    public $reference_number = '';
    public $notes = '';

    public $selectedSupplier = null;
    public $suggestedAllocations = [];
    public $outstandingGRNs = [];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'payment_date' => 'required|date',
        'amount' => 'required|numeric|min:0.01',
        'payment_mode' => 'required|in:cash,bank_transfer',
        'bank_account_id' => 'nullable|exists:accounts,id',
        'bank_reference' => 'nullable|string|max:100',
        'reference_number' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'bank_account_id.required' => 'Please select a bank account for bank transfer.',
        'bank_account_id.exists' => 'Selected bank account is invalid.',
    ];

    public function mount()
    {
        $this->payment_date = now()->format('Y-m-d');
    }

    public function updatedSupplierId($value)
    {
        if ($value) {
            $this->selectedSupplier = Supplier::find($value);
            $this->loadOutstandingGRNs();
            $this->updateAllocationSuggestions();
        } else {
            $this->selectedSupplier = null;
            $this->outstandingGRNs = [];
            $this->suggestedAllocations = [];
        }
    }

    public function updatedAmount($value)
    {
        if ($this->supplier_id && $value > 0) {
            $this->updateAllocationSuggestions();
        } else {
            $this->suggestedAllocations = [];
        }
    }

    protected function loadOutstandingGRNs()
    {
        if (!$this->supplier_id) {
            return;
        }

        $allocationService = app(PaymentAllocationService::class);
        $this->outstandingGRNs = $allocationService->getOutstandingGRNs($this->supplier_id)->toArray();
    }

    protected function updateAllocationSuggestions()
    {
        if (!$this->supplier_id || !$this->amount || $this->amount <= 0) {
            $this->suggestedAllocations = [];
            return;
        }

        $allocationService = app(PaymentAllocationService::class);
        $this->suggestedAllocations = $allocationService->getSuggestedAllocation(
            $this->supplier_id,
            $this->amount
        )->toArray();
    }

    public function save()
    {
        // Additional validation for bank transfer
        if ($this->payment_mode === 'bank_transfer' && empty($this->bank_account_id)) {
            $this->addError('bank_account_id', 'Please select a bank account for bank transfer.');
            return;
        }

        $this->validate();

        try {
            // Validate amount doesn't exceed outstanding
            if ($this->selectedSupplier && $this->amount > $this->selectedSupplier->outstanding_balance) {
                session()->flash('error', "Payment amount (₹{$this->amount}) cannot exceed outstanding balance (₹{$this->selectedSupplier->outstanding_balance})");
                return;
            }

            // Create supplier payment
            $payment = SupplierPayment::create([
                'supplier_id' => $this->supplier_id,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'payment_mode' => $this->payment_mode,
                'bank_account_id' => $this->payment_mode === 'bank_transfer' ? $this->bank_account_id : null,
                'bank_reference' => $this->bank_reference,
                'reference_number' => $this->reference_number,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            // Allocate payment to GRNs using water-fill logic
            $allocationService = app(PaymentAllocationService::class);
            $allocations = $allocationService->allocatePayment($payment);

            $allocationCount = count($allocations);
            session()->flash('success', "Payment recorded successfully and allocated to {$allocationCount} GRN(s)");
            return redirect()->route('suppliers.payments.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $suppliers = Supplier::active()
            ->withOutstanding()
            ->orderBy('name')
            ->get();

        // Get bank/cash accounts (asset type accounts)
        $bankAccounts = Account::active()
            ->where('account_type', 'asset')
            ->whereIn('account_name', ['Cash', 'Bank', 'Petty Cash'])
            ->orWhere(function($query) {
                $query->where('account_type', 'asset')
                      ->where('account_name', 'like', '%Bank%')
                      ->orWhere('account_name', 'like', '%Cash%');
            })
            ->orderBy('account_name')
            ->get();

        return view('livewire.suppliers.payments.record-payment', [
            'suppliers' => $suppliers,
            'bankAccounts' => $bankAccounts,
        ]);
    }
}
