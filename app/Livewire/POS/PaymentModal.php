<?php

namespace App\Livewire\POS;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Account;
use App\Services\InventoryService;
use App\Services\PrintService;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\DB;

class PaymentModal extends Component
{
    public $show = false;
    public $grandTotal = 0;
    public $cartData = [];

    // Multiple payment support
    public $payments = []; // Array of payment entries
    public $currentPaymentMode = 'cash';
    public $currentAmount = 0;
    public $currentBankAccount = null;

    public $totalPaid = 0;
    public $remainingAmount = 0;

    // Cash-specific
    public $cashReceived = 0;
    public $change = 0;

    // Processing state
    public $processing = false;

    protected $listeners = [
        'openPaymentModal' => 'open',
    ];

    public function open($data)
    {
        $this->grandTotal = $data['grandTotal'];
        $this->cartData = $data['cartData'];
        $this->show = true;

        // Reset
        $this->payments = [];
        $this->totalPaid = 0;
        $this->remainingAmount = $this->grandTotal;
        $this->cashReceived = 0;
        $this->change = 0;
        $this->currentAmount = 0;
        $this->currentPaymentMode = 'cash';
        $this->currentBankAccount = null;
    }

    public function addPayment()
    {
        $this->validate([
            'currentAmount' => 'required|numeric|min:0.01|max:' . $this->remainingAmount,
            'currentBankAccount' => 'required_if:currentPaymentMode,bank_transfer',
        ], [
            'currentAmount.max' => 'Amount cannot exceed remaining balance',
            'currentBankAccount.required_if' => 'Please select a bank account',
        ]);

        // Get bank account name if bank transfer
        $bankAccountName = null;
        if ($this->currentPaymentMode === 'bank_transfer' && $this->currentBankAccount) {
            $account = Account::find($this->currentBankAccount);
            $bankAccountName = $account ? $account->name : null;
        }

        // Add payment to array
        $this->payments[] = [
            'mode' => $this->currentPaymentMode,
            'amount' => $this->currentAmount,
            'bank_account_id' => $this->currentPaymentMode === 'bank_transfer'
                ? $this->currentBankAccount
                : null,
            'bank_account_name' => $bankAccountName,
        ];

        // Update totals
        $this->totalPaid += $this->currentAmount;
        $this->remainingAmount = $this->grandTotal - $this->totalPaid;

        // Reset current inputs
        $this->currentAmount = 0;
        $this->currentBankAccount = null;

        // If fully paid, enable confirm button
        if ($this->remainingAmount <= 0) {
            $this->remainingAmount = 0;
        }
    }

    public function removePayment($index)
    {
        if (isset($this->payments[$index])) {
            $this->totalPaid -= $this->payments[$index]['amount'];
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments);

            $this->remainingAmount = $this->grandTotal - $this->totalPaid;
        }
    }

    public function quickPayFull($mode)
    {
        $this->currentPaymentMode = $mode;
        $this->currentAmount = $this->remainingAmount;

        if ($mode === 'cash') {
            $this->cashReceived = $this->remainingAmount;
            $this->addPayment();
        }
        // For bank, user must select account first
    }

    public function updatedCashReceived()
    {
        if ($this->cashReceived >= $this->grandTotal) {
            $this->change = $this->cashReceived - $this->grandTotal;
        } else {
            $this->change = 0;
        }
    }

    public function confirmPayment()
    {
        // Validate full payment
        if ($this->totalPaid < $this->grandTotal) {
            session()->flash('error', 'Payment incomplete. Remaining: Rs. ' . number_format($this->remainingAmount, 2));
            return;
        }

        $this->processing = true;

        try {
            DB::transaction(function () {
                // Create sale
                $sale = Sale::create([
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'shift_id' => auth()->user()->currentShift->id,
                    'customer_id' => $this->cartData['customer_id'],
                    'sale_date' => now(),
                    'subtotal' => $this->cartData['subtotal'],
                    'discount_amount' => $this->cartData['discount'],
                    'discount_type' => $this->cartData['discount_type'],
                    'total_amount' => $this->cartData['total'],
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'points_earned' => 0, // Will be set by LoyaltyService
                    'created_by' => auth()->id(),
                ]);

                // Create sale items and reduce stock
                foreach ($this->cartData['items'] as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'is_box_sale' => $item['is_box_sale'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $item['item_discount'] ?? 0,
                        'total_price' => $item['total'],
                        'offer_id' => $item['offer_id'] ?? null,
                    ]);

                    // Reduce stock using InventoryService
                    $product = Product::find($item['product_id']);
                    app(InventoryService::class)->reduceStock($product, $item['quantity'], [
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                    ]);
                }

                // Create payment records (MULTIPLE PAYMENTS)
                foreach ($this->payments as $payment) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_mode' => $payment['mode'],
                        'bank_account_id' => $payment['bank_account_id'],
                        'amount' => $payment['amount'],
                    ]);
                }

                // Update shift totals
                $shift = auth()->user()->currentShift;
                $shift->increment('total_sales', $this->cartData['total']);
                $shift->increment('total_transactions');

                // Update cash and bank totals separately
                foreach ($this->payments as $payment) {
                    if ($payment['mode'] === 'cash') {
                        $shift->increment('total_cash_sales', $payment['amount']);
                    } else {
                        $shift->increment('total_bank_sales', $payment['amount']);
                    }
                }

                // Update customer total purchases and award loyalty points
                if ($this->cartData['customer_id']) {
                    $customer = Customer::find($this->cartData['customer_id']);
                    $customer->increment('total_purchases', $this->cartData['total']);

                    // Award loyalty points
                    app(LoyaltyService::class)->awardPoints($customer, $sale);
                }

                // Print receipt
                try {
                    app(PrintService::class)->printReceipt($sale);
                } catch (\Exception $e) {
                    logger()->error('Receipt printing failed: ' . $e->getMessage());
                }

                // Close modal and clear parent cart
                $this->show = false;
                $this->dispatch('paymentCompleted');

                // Show success message
                session()->flash('success', 'Sale completed! Invoice: ' . $sale->invoice_number);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            logger()->error('Payment processing error: ' . $e->getMessage());
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->show = false;
        $this->reset(['cashReceived', 'change', 'grandTotal', 'cartData', 'payments', 'totalPaid', 'remainingAmount']);
    }

    public function render()
    {
        $bankAccounts = Account::where('account_type', 'asset')
            ->whereIn('code', ['BANK1', 'BANK2'])
            ->get();

        return view('livewire.pos.payment-modal', [
            'bankAccounts' => $bankAccounts,
        ]);
    }
}
