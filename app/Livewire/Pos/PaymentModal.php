<?php

namespace App\Livewire\Pos;

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

    // Payment amount from customer
    public $paidAmount = 0;
    public $changeToReturn = 0;
    public $isCreditInvoice = false;

    // Customer selection
    public $showCustomerSelector = false;
    public $customerSearchTerm = '';
    public $selectedCustomerId = null;

    // Customer creation
    public $showCreateCustomer = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';

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
    public $showPaymentStep = 'amount'; // 'amount' or 'payment'

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
        $this->paidAmount = 0;
        $this->changeToReturn = 0;
        $this->isCreditInvoice = false;
        $this->showPaymentStep = 'amount';
        $this->showCustomerSelector = false;
        $this->customerSearchTerm = '';
        $this->selectedCustomerId = $this->cartData['customer_id'] ?? null;
    }

    /**
     * Calculate change when paid amount is updated
     */
    public function updatedPaidAmount()
    {
        if ($this->paidAmount >= $this->grandTotal) {
            $this->changeToReturn = $this->paidAmount - $this->grandTotal;
            $this->isCreditInvoice = false;
        } else {
            $this->changeToReturn = 0;
            $this->isCreditInvoice = $this->paidAmount > 0;
        }
    }

    /**
     * Process payment after amount is entered
     */
    public function processPaymentAmount()
    {
        $this->validate([
            'paidAmount' => 'required|numeric|min:0',
        ], [
            'paidAmount.required' => 'Please enter the amount received from customer',
            'paidAmount.min' => 'Amount cannot be negative',
        ]);

        // If credit invoice (including 0 payment), validate customer is selected
        if ($this->paidAmount < $this->grandTotal && !$this->selectedCustomerId && !$this->cartData['customer_id']) {
            // Show customer selector instead of just error
            $this->showCustomerSelector = true;
            session()->flash('error', 'Please select a customer for credit invoices');
            return;
        }

        // Update cart data with selected customer if changed
        if ($this->selectedCustomerId) {
            $this->cartData['customer_id'] = $this->selectedCustomerId;
        }

        // Proceed to complete payment directly
        $this->completePayment();
    }

    /**
     * Mark as full credit invoice (0 payment)
     */
    public function markAsFullCredit()
    {
        // Validate customer is selected
        if (!$this->selectedCustomerId && !$this->cartData['customer_id']) {
            // Show customer selector instead of just error
            $this->showCustomerSelector = true;
            session()->flash('error', 'Please select a customer for full credit invoices');
            return;
        }

        // Update cart data with selected customer if changed
        if ($this->selectedCustomerId) {
            $this->cartData['customer_id'] = $this->selectedCustomerId;
        }

        $this->paidAmount = 0;
        $this->isCreditInvoice = true;
        $this->changeToReturn = 0;
        $this->completePayment();
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
            $bankAccountName = $account ? $account->account_name : null;
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

    /**
     * Complete payment with the amount entered by cashier
     */
    public function completePayment()
    {
        $this->processing = true;

        try {
            // VALIDATE STOCK BEFORE CREATING SALE
            foreach ($this->cartData['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['name']}");
                }

                // Check if sufficient stock is available
                if ($product->current_stock_quantity < $item['quantity']) {
                    if ($item['is_box_sale']) {
                        throw new \Exception(
                            "Cannot sell {$product->name} as box. " .
                            "Need {$item['quantity']} pieces for 1 box, " .
                            "but only {$product->current_stock_quantity} pieces available in stock. " .
                            "Not enough pieces for box sale."
                        );
                    } else {
                        throw new \Exception(
                            "Insufficient stock for {$product->name}. " .
                            "Trying to sell {$item['quantity']} pieces, " .
                            "but only {$product->current_stock_quantity} pieces available in stock."
                        );
                    }
                }
            }

            DB::transaction(function () {
                // Determine payment status
                if ($this->paidAmount >= $this->grandTotal) {
                    $paymentStatus = 'paid';
                } elseif ($this->paidAmount > 0) {
                    $paymentStatus = 'partial';
                } else {
                    $paymentStatus = 'unpaid';
                }
                $actualPaidAmount = min($this->paidAmount, $this->grandTotal);

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
                    'payment_status' => $paymentStatus,
                    'status' => 'completed',
                    'points_earned' => 0, // Will be set by LoyaltyService
                    'notes' => $this->isCreditInvoice ? 'Credit Invoice - Partial Payment' : null,
                    'created_by' => auth()->id(),
                ]);

                // Create sale items and reduce stock
                foreach ($this->cartData['items'] as $item) {
                    // Reduce stock using InventoryService and get the stock movement
                    $product = Product::find($item['product_id']);

                    // If batch was selected in cart, use that batch's pricing
                    $details = [
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                    ];

                    // If specific batch was selected, get its details and track source batch
                    if (isset($item['batch_id']) && $item['batch_id']) {
                        $batchDetails = app(InventoryService::class)->getBatchDetails($item['batch_id']);
                        if ($batchDetails) {
                            $details['source_stock_movement_id'] = $item['batch_id']; // Track which batch is being depleted
                            $details['unit_cost'] = $batchDetails['unit_cost'];
                            $details['min_selling_price'] = $batchDetails['min_selling_price'];
                            $details['max_selling_price'] = $batchDetails['max_selling_price'];
                            $details['batch_number'] = $batchDetails['batch_number'];
                        }
                    }

                    $stockMovement = app(InventoryService::class)->reduceStock($product, $item['quantity'], $details);

                    // Create sale item with batch tracking and COGS
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'stock_movement_id' => $stockMovement->id, // Link to the stock movement (batch)
                        'quantity' => $item['quantity'],
                        'is_box_sale' => $item['is_box_sale'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost' => $stockMovement->unit_cost ?? $item['batch_cost'] ?? null, // Cost from selected/FIFO batch for COGS calculation
                        'discount_amount' => $item['item_discount'] ?? 0,
                        'total_price' => $item['total'],
                        'offer_id' => $item['offer_id'] ?? null,
                    ]);
                }

                // Create payment record (single payment - cash) only if amount > 0
                if ($actualPaidAmount > 0) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_mode' => 'cash',
                        'bank_account_id' => null,
                        'amount' => $actualPaidAmount,
                    ]);
                }

                // Update shift totals
                $shift = auth()->user()->currentShift;
                $shift->increment('total_sales', $actualPaidAmount);
                $shift->increment('total_transactions');
                $shift->increment('total_cash_sales', $actualPaidAmount);

                // Update customer total purchases and award loyalty points
                if ($this->cartData['customer_id']) {
                    $customer = Customer::find($this->cartData['customer_id']);
                    $customer->increment('total_purchases', $actualPaidAmount);

                    // Award loyalty points (only on paid amount for credit invoices)
                    app(LoyaltyService::class)->awardPoints($customer, $sale);
                }

                // Print receipt
                try {
                    app(PrintService::class)->printReceipt($sale);
                } catch (\Exception $e) {
                    logger()->error('Receipt printing failed: ' . $e->getMessage());
                }

                // Store sale ID for print preview
                session(['last_sale_id' => $sale->id]);

                // Close modal and clear parent cart
                $this->show = false;
                $this->dispatch('paymentCompleted', saleId: $sale->id);

                // Show success message
                $message = 'Sale completed! Invoice: ' . $sale->invoice_number;
                if ($this->changeToReturn > 0) {
                    $message .= ' | Change to return: Rs. ' . number_format($this->changeToReturn, 2);
                }
                if ($this->paidAmount == 0) {
                    $message .= ' | FULL CREDIT INVOICE - Total due: Rs. ' . number_format($this->grandTotal, 2);
                } elseif ($this->isCreditInvoice) {
                    $message .= ' | CREDIT INVOICE - Balance due: Rs. ' . number_format($this->grandTotal - $actualPaidAmount, 2);
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            logger()->error('Payment processing error: ' . $e->getMessage());
        } finally {
            $this->processing = false;
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
            // VALIDATE STOCK BEFORE CREATING SALE
            foreach ($this->cartData['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['name']}");
                }

                // Check if sufficient stock is available
                if ($product->current_stock_quantity < $item['quantity']) {
                    if ($item['is_box_sale']) {
                        throw new \Exception(
                            "Cannot sell {$product->name} as box. " .
                            "Need {$item['quantity']} pieces for 1 box, " .
                            "but only {$product->current_stock_quantity} pieces available in stock. " .
                            "Not enough pieces for box sale."
                        );
                    } else {
                        throw new \Exception(
                            "Insufficient stock for {$product->name}. " .
                            "Trying to sell {$item['quantity']} pieces, " .
                            "but only {$product->current_stock_quantity} pieces available in stock."
                        );
                    }
                }
            }

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
                    // Reduce stock using InventoryService and get the stock movement
                    $product = Product::find($item['product_id']);

                    // If batch was selected in cart, use that batch's pricing
                    $details = [
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                    ];

                    // If specific batch was selected, get its details and track source batch
                    if (isset($item['batch_id']) && $item['batch_id']) {
                        $batchDetails = app(InventoryService::class)->getBatchDetails($item['batch_id']);
                        if ($batchDetails) {
                            $details['source_stock_movement_id'] = $item['batch_id']; // Track which batch is being depleted
                            $details['unit_cost'] = $batchDetails['unit_cost'];
                            $details['min_selling_price'] = $batchDetails['min_selling_price'];
                            $details['max_selling_price'] = $batchDetails['max_selling_price'];
                            $details['batch_number'] = $batchDetails['batch_number'];
                        }
                    }

                    $stockMovement = app(InventoryService::class)->reduceStock($product, $item['quantity'], $details);

                    // Create sale item with batch tracking and COGS
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'stock_movement_id' => $stockMovement->id, // Link to the stock movement (batch)
                        'quantity' => $item['quantity'],
                        'is_box_sale' => $item['is_box_sale'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost' => $stockMovement->unit_cost ?? $item['batch_cost'] ?? null, // Cost from selected/FIFO batch for COGS calculation
                        'discount_amount' => $item['item_discount'] ?? 0,
                        'total_price' => $item['total'],
                        'offer_id' => $item['offer_id'] ?? null,
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

                // Store sale ID for print preview
                session(['last_sale_id' => $sale->id]);

                // Close modal and clear parent cart
                $this->show = false;
                $this->dispatch('paymentCompleted', saleId: $sale->id);

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

    /**
     * Select customer from the payment modal
     */
    public function selectCustomer($customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->cartData['customer_id'] = $customerId;
        $this->showCustomerSelector = false;

        $customer = Customer::find($customerId);
        session()->flash('success', 'Customer selected: ' . $customer->name);
    }

    /**
     * Open customer selector
     */
    public function openCustomerSelector()
    {
        $this->showCustomerSelector = true;
    }

    /**
     * Close customer selector
     */
    public function closeCustomerSelector()
    {
        $this->showCustomerSelector = false;
        $this->showCreateCustomer = false;
        $this->customerSearchTerm = '';
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
    }

    /**
     * Open customer creation form
     */
    public function openCreateCustomer()
    {
        $this->showCreateCustomer = true;

        // Pre-fill from search term if it looks like a phone number
        if (preg_match('/^[0-9]{10,}/', $this->customerSearchTerm)) {
            $this->newCustomerPhone = $this->customerSearchTerm;
        } else {
            $this->newCustomerName = $this->customerSearchTerm;
        }
    }

    /**
     * Go back to customer list from creation form
     */
    public function backToCustomerList()
    {
        $this->showCreateCustomer = false;
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->resetErrorBag();
    }

    /**
     * Create a new customer from payment modal
     */
    public function createCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|max:255',
            'newCustomerPhone' => 'required|unique:customers,phone|max:20',
        ], [
            'newCustomerName.required' => 'Customer name is required',
            'newCustomerPhone.required' => 'Phone number is required',
            'newCustomerPhone.unique' => 'This phone number is already registered',
        ]);

        try {
            $customer = Customer::create([
                'customer_code' => Customer::generateCustomerCode(),
                'name' => $this->newCustomerName,
                'phone' => $this->newCustomerPhone,
                'is_active' => true,
            ]);

            // Auto-select the newly created customer
            $this->selectedCustomerId = $customer->id;
            $this->cartData['customer_id'] = $customer->id;

            // Close modals and reset
            $this->showCustomerSelector = false;
            $this->showCreateCustomer = false;
            $this->customerSearchTerm = '';
            $this->newCustomerName = '';
            $this->newCustomerPhone = '';

            session()->flash('success', 'Customer created and selected: ' . $customer->name);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->show = false;
        $this->reset(['cashReceived', 'change', 'grandTotal', 'cartData', 'payments', 'totalPaid', 'remainingAmount']);
    }

    public function render()
    {
        // Get all active asset accounts for bank transfers
        // This includes cash and bank accounts
        $bankAccounts = Account::where('account_type', 'asset')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();

        // Get customers for selection
        $customers = Customer::active()
            ->when($this->customerSearchTerm, function($query) {
                $query->where('name', 'like', '%' . $this->customerSearchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearchTerm . '%');
            })
            ->limit(10)
            ->get();

        return view('livewire.pos.payment-modal', [
            'bankAccounts' => $bankAccounts,
            'customers' => $customers,
        ]);
    }
}
