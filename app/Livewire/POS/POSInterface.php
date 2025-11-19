<?php

namespace App\Livewire\POS;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use App\Models\Customer;
use App\Services\POSService;
use App\Services\OfferService;
use Illuminate\Support\Str;

class POSInterface extends Component
{
    // Cart state
    public $cartItems = [];
    public $customerId = null;
    public $selectedCustomer = null;

    // Discounts
    public $cartDiscount = 0;
    public $cartDiscountType = 'fixed'; // fixed or percentage

    // Totals
    public $subtotal = 0;
    public $totalDiscount = 0;
    public $grandTotal = 0;

    // Hold bills
    public $heldBills = [];
    public $showHoldBillsModal = false;

    // Customer search
    public $customerSearchTerm = '';
    public $showCustomerModal = false;

    protected $listeners = [
        'productAdded' => 'addToCart',
        'paymentCompleted' => 'handlePaymentCompleted',
    ];

    public $lastSaleId = null;

    public function mount()
    {
        $this->loadHeldBills();
    }

    /**
     * Add product to cart
     */
    public function addToCart($productId, $isBoxSale = false, $batchId = null)
    {
        // Fresh query to get latest stock quantity
        $product = Product::with('packaging')->find($productId);

        if (!$product) {
            $this->dispatch('showToast', type: 'error', message: 'Product not found');
            return;
        }

        // Calculate quantity
        $quantity = $isBoxSale && $product->packaging
            ? $product->packaging->pieces_per_package
            : 1;

        // Check stock with detailed error message
        if (!app(POSService::class)->checkStock($product, $quantity)) {
            if ($isBoxSale) {
                $message = "Cannot sell {$product->name} as box. " .
                           "Need {$quantity} pieces for 1 box, " .
                           "but only {$product->current_stock_quantity} pieces available in stock. " .
                           "Not enough pieces for box sale.";
            } else {
                $message = "Insufficient stock for {$product->name}. " .
                           "Trying to add {$quantity} piece, " .
                           "but only {$product->current_stock_quantity} pieces available.";
            }
            $this->dispatch('showToast', type: 'error', message: $message);
            return;
        }

        // Get batch details if batch is selected, otherwise use FIFO
        $inventoryService = app(\App\Services\InventoryService::class);
        $batchDetails = null;

        if ($batchId) {
            $batchDetails = $inventoryService->getBatchDetails($batchId);
        } else {
            // Auto-select FIFO batch
            $fifoBatch = $inventoryService->getFIFOBatch($product);
            $batchDetails = [
                'stock_movement_id' => $fifoBatch['stock_movement_id'],
                'unit_cost' => $fifoBatch['unit_cost'],
                'min_selling_price' => $fifoBatch['min_selling_price'],
                'max_selling_price' => $fifoBatch['max_selling_price'],
                'batch_number' => $fifoBatch['batch_number'],
            ];
        }

        // Check if already in cart (same product, box_sale type, and batch)
        $cartKey = $this->findInCartWithBatch($productId, $isBoxSale, $batchDetails['stock_movement_id'] ?? null);

        if ($cartKey !== null) {
            // Check if adding more quantity would exceed available stock
            $existingQuantity = $this->cartItems[$cartKey]['quantity'];
            $newTotalQuantity = $existingQuantity + $quantity;

            if ($newTotalQuantity > $product->current_stock_quantity) {
                $maxCanAdd = $product->current_stock_quantity - $existingQuantity;

                if ($isBoxSale) {
                    $message = "Cannot add another box of {$product->name}. " .
                               "Cart already has {$existingQuantity} pieces. " .
                               "Need {$quantity} more pieces for 1 box, " .
                               "but only {$product->current_stock_quantity} pieces total available in stock. " .
                               "Not enough pieces for box sale. " .
                               "You can only add {$maxCanAdd} more pieces individually.";
                } else {
                    $message = "Cannot add more {$product->name}. " .
                               "Cart already has {$existingQuantity} pieces. " .
                               "Only {$product->current_stock_quantity} pieces total available in stock. " .
                               "Maximum you can add: {$maxCanAdd} more pieces.";
                }
                $this->dispatch('showToast', type: 'error', message: $message);
                return;
            }

            // Increment existing item
            $this->cartItems[$cartKey]['quantity'] = $newTotalQuantity;
        } else {
            // Add new item
            $minPrice = $batchDetails['min_selling_price'] ?? $product->min_selling_price;
            $maxPrice = $batchDetails['max_selling_price'] ?? $product->max_selling_price;

            $this->cartItems[] = [
                'id' => Str::uuid()->toString(),
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'is_box_sale' => $isBoxSale,
                'quantity' => $quantity,
                'unit_price' => $maxPrice, // Default to max price (MRP)
                'min_selling_price' => $minPrice,
                'max_selling_price' => $maxPrice,
                'can_adjust_price' => !is_null($minPrice) && $minPrice < $maxPrice,
                'batch_id' => $batchDetails['stock_movement_id'] ?? null,
                'batch_number' => $batchDetails['batch_number'] ?? 'N/A',
                'batch_cost' => $batchDetails['unit_cost'] ?? null,
                'item_discount' => 0,
                'offer_id' => null,
                'offer_discount' => 0,
                'offer_description' => null,
                'total' => 0,
            ];
        }

        $this->calculateTotals();
        $this->dispatch('showToast', type: 'success', message: $product->name . ' added to cart');
    }

    /**
     * Find item in cart considering batch
     */
    private function findInCartWithBatch($productId, $isBoxSale, $batchId)
    {
        foreach ($this->cartItems as $key => $item) {
            if ($item['product_id'] == $productId &&
                $item['is_box_sale'] == $isBoxSale &&
                ($item['batch_id'] ?? null) == $batchId) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($cartId, $newQuantity)
    {
        $key = $this->findCartKeyById($cartId);

        if ($key === null) {
            return;
        }

        if ($newQuantity <= 0) {
            $this->removeItem($cartId);
            return;
        }

        // Check stock with detailed error message
        $item = $this->cartItems[$key];
        $product = Product::find($item['product_id']);

        if (!app(POSService::class)->checkStock($product, $newQuantity)) {
            $saleType = $item['is_box_sale'] ? 'box' : 'piece';
            $message = "Cannot update quantity for {$product->name}. " .
                       "You are trying to set quantity to {$newQuantity} {$saleType}, " .
                       "but only {$product->current_stock_quantity} pieces available in stock.";
            $this->dispatch('showToast', type: 'error', message: $message);
            return;
        }

        $this->cartItems[$key]['quantity'] = $newQuantity;
        $this->calculateTotals();
    }

    /**
     * Update item price (between min and max selling price)
     */
    public function updatePrice($cartId, $newPrice)
    {
        $key = $this->findCartKeyById($cartId);

        if ($key === null) {
            return;
        }

        $item = $this->cartItems[$key];

        // Check if price adjustment is allowed
        if (!$item['can_adjust_price']) {
            $this->dispatch('showToast', type: 'error', message: 'Price adjustment not available for this product');
            return;
        }

        // Validate price is within range
        $minPrice = $item['min_selling_price'];
        $maxPrice = $item['max_selling_price'];

        if ($newPrice < $minPrice) {
            $this->dispatch('showToast', type: 'error', message: 'Price cannot be less than ₹' . number_format($minPrice, 2));
            return;
        }

        if ($newPrice > $maxPrice) {
            $this->dispatch('showToast', type: 'error', message: 'Price cannot be more than ₹' . number_format($maxPrice, 2));
            return;
        }

        $this->cartItems[$key]['unit_price'] = $newPrice;
        $this->calculateTotals();

        $this->dispatch('showToast', type: 'success', message: 'Price updated to ₹' . number_format($newPrice, 2));
    }

    /**
     * Remove item from cart
     */
    public function removeItem($cartId)
    {
        $key = $this->findCartKeyById($cartId);

        if ($key !== null) {
            unset($this->cartItems[$key]);
            $this->cartItems = array_values($this->cartItems);
            $this->calculateTotals();
        }
    }

    /**
     * Calculate all totals
     */
    public function calculateTotals()
    {
        $this->subtotal = 0;
        $posService = app(POSService::class);
        $offerService = app(OfferService::class);

        foreach ($this->cartItems as &$item) {
            $product = Product::with('packaging')->find($item['product_id']);

            if (!$product) {
                continue;
            }

            // Calculate base pricing (includes box discount)
            $pricing = $posService->calculateItemPrice(
                $product,
                $item['quantity'],
                $item['is_box_sale']
            );

            $item['unit_price'] = $pricing['unit_price'];
            $item['item_discount'] = $pricing['discount'];

            // Apply offers (best offer auto-applies)
            $offer = $offerService->findBestOffer(
                $product,
                $item['quantity'],
                $pricing['final_total']
            );

            if ($offer) {
                $item['offer_id'] = $offer['offer_id'];
                $item['offer_discount'] = $offer['discount_amount'];
                $item['offer_description'] = $offer['description'];
            } else {
                $item['offer_id'] = null;
                $item['offer_discount'] = 0;
                $item['offer_description'] = null;
            }

            // Final total = base price - box discount - offer discount
            $item['total'] = $pricing['final_total'] - ($item['offer_discount'] ?? 0);
            $this->subtotal += $item['total'];
        }

        // Calculate cart discount
        if ($this->cartDiscountType === 'percentage') {
            $this->totalDiscount = ($this->subtotal * $this->cartDiscount) / 100;
        } else {
            $this->totalDiscount = min($this->cartDiscount, $this->subtotal);
        }

        $this->grandTotal = max(0, $this->subtotal - $this->totalDiscount);
    }

    /**
     * Update cart discount
     */
    public function updatedCartDiscount()
    {
        $this->calculateTotals();
    }

    public function updatedCartDiscountType()
    {
        $this->calculateTotals();
    }

    /**
     * Hold current bill
     */
    public function holdBill()
    {
        if (empty($this->cartItems)) {
            $this->dispatch('showToast', type: 'warning', message: 'Cart is empty');
            return;
        }

        $this->heldBills[] = [
            'id' => Str::uuid()->toString(),
            'time' => now()->format('H:i:s'),
            'items' => $this->cartItems,
            'customer_id' => $this->customerId,
            'customer_name' => $this->selectedCustomer?->name ?? 'Walk-in',
            'discount' => $this->cartDiscount,
            'discount_type' => $this->cartDiscountType,
            'total' => $this->grandTotal,
            'item_count' => count($this->cartItems),
        ];

        session(['held_bills' => $this->heldBills]);

        $this->clearCart();
        $this->dispatch('showToast', type: 'success', message: 'Bill held successfully');
    }

    /**
     * Retrieve held bill
     */
    public function retrieveBill($holdId)
    {
        $key = array_search($holdId, array_column($this->heldBills, 'id'));

        if ($key === false) {
            return;
        }

        $bill = $this->heldBills[$key];

        $this->cartItems = $bill['items'];
        $this->customerId = $bill['customer_id'];
        $this->selectedCustomer = $this->customerId ? Customer::find($this->customerId) : null;
        $this->cartDiscount = $bill['discount'];
        $this->cartDiscountType = $bill['discount_type'];

        unset($this->heldBills[$key]);
        $this->heldBills = array_values($this->heldBills);
        session(['held_bills' => $this->heldBills]);

        $this->calculateTotals();
        $this->showHoldBillsModal = false;

        $this->dispatch('showToast', type: 'success', message: 'Bill retrieved');
    }

    /**
     * Delete held bill
     */
    public function deleteHeldBill($holdId)
    {
        $key = array_search($holdId, array_column($this->heldBills, 'id'));

        if ($key !== false) {
            unset($this->heldBills[$key]);
            $this->heldBills = array_values($this->heldBills);
            session(['held_bills' => $this->heldBills]);

            $this->dispatch('showToast', type: 'info', message: 'Held bill deleted');
        }
    }

    /**
     * Clear cart
     */
    public function clearCart()
    {
        $this->reset([
            'cartItems',
            'customerId',
            'selectedCustomer',
            'cartDiscount',
            'cartDiscountType',
            'subtotal',
            'totalDiscount',
            'grandTotal'
        ]);
    }

    /**
     * Select customer
     */
    public function selectCustomer($customerId)
    {
        $this->customerId = $customerId;
        $this->selectedCustomer = Customer::find($customerId);
        $this->showCustomerModal = false;

        $this->dispatch('showToast', type: 'success', message: 'Customer selected: ' . $this->selectedCustomer->name);
    }

    /**
     * Remove customer
     */
    public function removeCustomer()
    {
        $this->customerId = null;
        $this->selectedCustomer = null;
    }

    /**
     * Proceed to payment
     */
    public function proceedToPayment()
    {
        if (empty($this->cartItems)) {
            $this->dispatch('showToast', type: 'warning', message: 'Cart is empty');
            return;
        }

        // Dispatch to PaymentModal component
        $this->dispatch('openPaymentModal', [
            'grandTotal' => $this->grandTotal,
            'cartData' => [
                'items' => $this->cartItems,
                'customer_id' => $this->customerId,
                'subtotal' => $this->subtotal,
                'discount' => $this->totalDiscount,
                'discount_type' => $this->cartDiscountType,
                'total' => $this->grandTotal,
            ]
        ]);
    }

    /**
     * Handle payment completed event
     */
    public function handlePaymentCompleted($saleId)
    {
        $this->clearCart();
        $this->lastSaleId = $saleId;

        // Dispatch browser event to open print preview in new tab
        $this->dispatch('openPrintPreview', saleId: $saleId);

        $this->dispatch('showToast', type: 'success', message: 'Sale completed successfully! Print preview opened in new tab.');
    }

    // Helper methods
    private function findInCart($productId, $isBoxSale)
    {
        foreach ($this->cartItems as $key => $item) {
            if ($item['product_id'] == $productId && $item['is_box_sale'] == $isBoxSale) {
                return $key;
            }
        }
        return null;
    }

    private function findCartKeyById($cartId)
    {
        return array_search($cartId, array_column($this->cartItems, 'id'));
    }

    private function loadHeldBills()
    {
        $this->heldBills = session('held_bills', []);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $customers = Customer::active()
            ->when($this->customerSearchTerm, function($query) {
                $query->where('name', 'like', '%' . $this->customerSearchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $this->customerSearchTerm . '%');
            })
            ->limit(10)
            ->get();

        return view('livewire.pos.pos-interface', [
            'customers' => $customers,
        ]);
    }
}
