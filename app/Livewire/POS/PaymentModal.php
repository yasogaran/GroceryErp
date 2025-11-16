<?php

namespace App\Livewire\POS;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Product;
use App\Models\Customer;
use App\Services\InventoryService;
use App\Services\PrintService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class PaymentModal extends Component
{
    public $show = false;
    public $grandTotal = 0;
    public $cartData = [];

    // Payment inputs
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
        $this->cashReceived = 0;
        $this->change = 0;
    }

    public function updatedCashReceived()
    {
        $this->change = max(0, $this->cashReceived - $this->grandTotal);
    }

    public function quickTender($amount)
    {
        $this->cashReceived = $amount;
    }

    public function confirmPayment()
    {
        $this->validate([
            'cashReceived' => [
                'required',
                'numeric',
                'min:' . $this->grandTotal,
            ],
        ], [
            'cashReceived.min' => 'Cash received must be at least Rs. ' . number_format($this->grandTotal, 2),
        ]);

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
                        'discount_amount' => $item['item_discount'],
                        'total_price' => $item['total'],
                    ]);

                    // Reduce stock using InventoryService
                    $product = Product::find($item['product_id']);
                    app(InventoryService::class)->reduceStock($product, $item['quantity'], [
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                    ]);
                }

                // Create payment record
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_mode' => 'cash',
                    'amount' => $this->cartData['total'],
                ]);

                // Update shift totals
                $shift = auth()->user()->currentShift;
                $shift->increment('total_sales', $this->cartData['total']);
                $shift->increment('total_cash_sales', $this->cartData['total']);
                $shift->increment('total_transactions');

                // Update customer total purchases
                if ($this->cartData['customer_id']) {
                    $customer = Customer::find($this->cartData['customer_id']);
                    $customer->increment('total_purchases', $this->cartData['total']);
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
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->show = false;
        $this->reset(['cashReceived', 'change', 'grandTotal', 'cartData']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pos.payment-modal');
    }
}
