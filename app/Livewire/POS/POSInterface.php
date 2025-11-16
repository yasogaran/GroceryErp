<?php

namespace App\Livewire\POS;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use App\Models\Customer;
use App\Services\POSService;
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

    public function mount()
    {
        $this->loadHeldBills();
    }

    /**
     * Add product to cart
     */
    public function addToCart($productId, $isBoxSale = false)
    {
        $product = Product::with('packaging')->find($productId);

        if (!$product) {
            session()->flash('error', 'Product not found');
            return;
        }

        // Calculate quantity
        $quantity = $isBoxSale && $product->packaging
            ? $product->packaging->pieces_per_package
            : 1;

        // Check stock
        if (!app(POSService::class)->checkStock($product, $quantity)) {
            session()->flash('error', 'Insufficient stock');
            return;
        }

        // Check if already in cart
        $cartKey = $this->findInCart($productId, $isBoxSale);

        if ($cartKey !== null) {
            // Increment existing item
            $this->cartItems[$cartKey]['quantity'] += $quantity;
        } else {
            // Add new item
            $this->cartItems[] = [
                'id' => Str::uuid()->toString(),
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'is_box_sale' => $isBoxSale,
                'quantity' => $quantity,
                'unit_price' => $product->max_selling_price,
                'item_discount' => 0,
                'total' => 0,
            ];
        }

        $this->calculateTotals();
        session()->flash('success', $product->name . ' added to cart');
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

        // Check stock
        $item = $this->cartItems[$key];
        $product = Product::find($item['product_id']);

        if (!app(POSService::class)->checkStock($product, $newQuantity)) {
            session()->flash('error', 'Insufficient stock');
            return;
        }

        $this->cartItems[$key]['quantity'] = $newQuantity;
        $this->calculateTotals();
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

        foreach ($this->cartItems as &$item) {
            $product = Product::with('packaging')->find($item['product_id']);

            if (!$product) {
                continue;
            }

            $pricing = $posService->calculateItemPrice(
                $product,
                $item['quantity'],
                $item['is_box_sale']
            );

            $item['unit_price'] = $pricing['unit_price'];
            $item['item_discount'] = $pricing['discount'];
            $item['total'] = $pricing['final_total'];

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
            session()->flash('warning', 'Cart is empty');
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
        session()->flash('success', 'Bill held successfully');
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

        session()->flash('success', 'Bill retrieved');
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

            session()->flash('info', 'Held bill deleted');
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

        session()->flash('success', 'Customer selected: ' . $this->selectedCustomer->name);
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
            session()->flash('warning', 'Cart is empty');
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
    public function handlePaymentCompleted()
    {
        $this->clearCart();
        session()->flash('success', 'Sale completed successfully!');
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

    #[Layout('layouts.app')]
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
