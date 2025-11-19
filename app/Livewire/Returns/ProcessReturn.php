<?php

namespace App\Livewire\Returns;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Account;
use App\Models\Shift;
use App\Services\ReturnService;
use App\Traits\WithToast;

class ProcessReturn extends Component
{
    use WithToast;
    // Step 1: Search sale
    public $invoiceSearch = '';
    public $selectedSale = null;

    // Step 2: Select items
    public $returnItems = [];

    // Step 3: Refund details
    public $refundMode = 'cash';
    public $bankAccountId = null;
    public $returnReason = '';
    public $totalRefund = 0;
    public $deductFromShift = false;

    // UI State
    public $step = 1; // 1: Search, 2: Select Items, 3: Process Refund
    public $activeShift = null;

    protected $rules = [
        'returnItems.*.quantity' => 'required|numeric|min:0.01',
        'returnItems.*.is_damaged' => 'boolean',
        'returnReason' => 'required|string|max:500',
        'refundMode' => 'required|in:cash,bank_transfer',
        'bankAccountId' => 'required_if:refundMode,bank_transfer',
    ];

    public function mount()
    {
        // Check if current user has an active shift
        $this->activeShift = Shift::where('cashier_id', auth()->id())
            ->open()
            ->first();
    }

    public function searchSale()
    {
        $this->validate([
            'invoiceSearch' => 'required',
        ]);

        $this->selectedSale = Sale::with(['items.product', 'customer', 'payments', 'shift'])
            ->where('invoice_number', $this->invoiceSearch)
            ->orWhere('id', $this->invoiceSearch)
            ->first();

        if (!$this->selectedSale) {
            $this->toastError('Invoice not found');
            return;
        }

        // Check if invoice has any due amount
        if ($this->selectedSale->hasDueAmount()) {
            $dueAmount = number_format($this->selectedSale->due_amount, 2);
            $this->toastError("This invoice has a due amount of " . settings('currency_symbol', 'Rs.') . " {$dueAmount}. Please complete the payment before processing a return.");
            $this->selectedSale = null;
            return;
        }

        // Initialize return items
        $this->returnItems = [];
        foreach ($this->selectedSale->items as $item) {
            // Calculate already returned quantity
            $alreadyReturned = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)
                ->sum('returned_quantity');

            $remainingQuantity = $item->quantity - $alreadyReturned;

            if ($remainingQuantity > 0) {
                $this->returnItems[] = [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'original_quantity' => $item->quantity,
                    'already_returned' => $alreadyReturned,
                    'remaining_quantity' => $remainingQuantity,
                    'quantity' => 0,
                    'is_damaged' => false,
                    'unit_price' => $item->total_price / $item->quantity,
                    'refund_amount' => 0,
                    'selected' => false,
                ];
            }
        }

        if (empty($this->returnItems)) {
            $this->toastWarning('All items have already been returned');
            return;
        }

        $this->step = 2;
    }

    public function updatedReturnItems()
    {
        $this->calculateTotalRefund();
    }

    public function calculateTotalRefund()
    {
        $returnService = app(ReturnService::class);
        $this->totalRefund = 0;

        foreach ($this->returnItems as $key => $item) {
            if ($item['selected'] && $item['quantity'] > 0) {
                // Get actual sale item for precise calculation
                $saleItem = SaleItem::find($item['sale_item_id']);
                $refund = $returnService->calculateRefundAmount($saleItem, $item['quantity']);

                $this->returnItems[$key]['refund_amount'] = $refund;
                $this->totalRefund += $refund;
            } else {
                $this->returnItems[$key]['refund_amount'] = 0;
            }
        }
    }

    public function proceedToRefund()
    {
        // Validate at least one item selected
        $hasSelection = collect($this->returnItems)->where('selected', true)->isNotEmpty();

        if (!$hasSelection) {
            $this->toastError('Please select at least one item to return');
            return;
        }

        $this->step = 3;

        // Auto-select refund mode from original payment
        if ($this->selectedSale->payments->first()) {
            $this->refundMode = $this->selectedSale->payments->first()->payment_mode;
            $this->bankAccountId = $this->selectedSale->payments->first()->bank_account_id;
        }
    }

    public function processReturn()
    {
        try {
            $this->validate();

            $returnService = app(ReturnService::class);

            // Prepare return items data
            $selectedItems = collect($this->returnItems)
                ->where('selected', true)
                ->map(function($item) {
                    return [
                        'sale_item_id' => $item['sale_item_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'refund_amount' => $item['refund_amount'],
                        'is_damaged' => $item['is_damaged'],
                    ];
                })
                ->values()
                ->toArray();

            // Validate quantities
            $errors = $returnService->validateReturnQuantities($this->selectedSale, $selectedItems);

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addError('returnItems', $error);
                }
                $this->toastError('Please fix the validation errors');
                return;
            }

            $return = $returnService->processReturn([
                'sale_id' => $this->selectedSale->id,
                'customer_id' => $this->selectedSale->customer_id,
                'total_refund_amount' => $this->totalRefund,
                'refund_mode' => $this->refundMode,
                'bank_account_id' => $this->bankAccountId,
                'reason' => $this->returnReason,
                'items' => $selectedItems,
            ]);

            // Deduct from shift if requested and refund is cash
            if ($this->deductFromShift && $this->refundMode === 'cash' && $this->activeShift) {
                $this->activeShift->decrement('total_cash_sales', $this->totalRefund);
                $this->activeShift->decrement('total_sales', $this->totalRefund);
            }

            $this->toastSuccess('Return processed successfully! Return Number: ' . $return->return_number);

            // Reset form
            $this->reset();
            $this->step = 1;

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->toastValidationErrors($e);
            throw $e;
        } catch (\Exception $e) {
            $this->toastError('Error processing return: ' . $e->getMessage());
        }
    }

    public function backToSearch()
    {
        $this->reset();
        $this->step = 1;
    }

    public function backToItems()
    {
        $this->step = 2;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Get bank/cash accounts (asset type accounts)
        $bankAccounts = Account::active()
            ->where('account_type', 'asset')
            ->where(function($query) {
                $query->whereIn('account_name', ['Cash', 'Bank', 'Petty Cash'])
                      ->orWhere('account_name', 'like', '%Bank%')
                      ->orWhere('account_name', 'like', '%Cash%');
            })
            ->orderBy('account_name')
            ->get();

        return view('livewire.returns.process-return', [
            'bankAccounts' => $bankAccounts,
        ]);
    }
}
