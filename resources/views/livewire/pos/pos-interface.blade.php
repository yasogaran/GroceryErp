<div class="h-screen flex flex-col bg-gray-50">
    <!-- Top Action Bar -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold text-gray-800">Point of Sale</h1>

            <!-- Customer Selection -->
            <div class="flex items-center space-x-2">
                @if($selectedCustomer)
                    <div class="bg-blue-100 px-4 py-2 rounded-lg flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="font-medium text-blue-800">{{ $selectedCustomer->name }}</span>
                        <button wire:click="removeCustomer" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @else
                    <button
                        wire:click="$set('showCustomerModal', true)"
                        class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-gray-700">Select Customer</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2">
            <!-- Held Bills -->
            <button
                wire:click="$set('showHoldBillsModal', true)"
                class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-4 py-2 rounded-lg flex items-center space-x-2 relative"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <span>Held Bills</span>
                @if(count($heldBills) > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                        {{ count($heldBills) }}
                    </span>
                @endif
            </button>

            <!-- Hold Bill -->
            <button
                wire:click="holdBill"
                class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-4 py-2 rounded-lg flex items-center space-x-2"
            >
                <span>Hold (F3)</span>
            </button>

            <!-- Clear Cart -->
            <button
                wire:click="clearCart"
                wire:confirm="Are you sure you want to clear the cart?"
                class="bg-red-100 hover:bg-red-200 text-red-800 px-4 py-2 rounded-lg flex items-center space-x-2"
            >
                <span>Clear (ESC)</span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mx-4 mt-2 rounded" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mx-4 mt-2 rounded" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content: Split Screen -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left: Product Search (60%) -->
        <div class="w-3/5 p-4 overflow-hidden">
            @livewire('pos.product-search')
        </div>

        <!-- Right: Cart (40%) -->
        <div class="w-2/5 bg-white border-l border-gray-200 p-4 flex flex-col">
            <!-- Cart Header -->
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-800">Cart</h2>
                <p class="text-sm text-gray-500">{{ count($cartItems) }} item(s)</p>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto mb-4 space-y-2">
                @forelse($cartItems as $item)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800">{{ $item['name'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $item['sku'] }}</p>

                                @if($item['is_box_sale'])
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mt-1">
                                        BOX
                                    </span>
                                @endif
                            </div>

                            <button
                                wire:click="removeItem('{{ $item['id'] }}')"
                                class="text-red-500 hover:text-red-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-2">
                                <button
                                    wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                    class="bg-gray-200 hover:bg-gray-300 w-8 h-8 rounded flex items-center justify-center"
                                >
                                    <span class="text-lg font-bold">-</span>
                                </button>

                                <input
                                    type="number"
                                    value="{{ $item['quantity'] }}"
                                    wire:change="updateQuantity('{{ $item['id'] }}', $event.target.value)"
                                    class="w-16 text-center border border-gray-300 rounded py-1"
                                    min="1"
                                >

                                <button
                                    wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                    class="bg-gray-200 hover:bg-gray-300 w-8 h-8 rounded flex items-center justify-center"
                                >
                                    <span class="text-lg font-bold">+</span>
                                </button>
                            </div>

                            <!-- Price -->
                            <div class="text-right">
                                <p class="text-sm text-gray-600">
                                    @ {{ format_currency($item['unit_price']) }}
                                </p>

                                @if($item['item_discount'] > 0)
                                    <p class="text-xs text-green-600">
                                        -{{ format_currency($item['item_discount']) }}
                                    </p>
                                @endif

                                <p class="text-lg font-bold text-gray-800">
                                    {{ format_currency($item['total']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p>Cart is empty</p>
                        <p class="text-sm">Scan or search products to add</p>
                    </div>
                @endforelse
            </div>

            <!-- Cart Discount -->
            @if(count($cartItems) > 0)
                <div class="border-t border-gray-200 pt-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cart Discount</label>
                    <div class="flex space-x-2">
                        <input
                            type="number"
                            wire:model.blur="cartDiscount"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2"
                            placeholder="0"
                            step="0.01"
                            min="0"
                        >
                        <select
                            wire:model.change="cartDiscountType"
                            class="border border-gray-300 rounded-lg px-3 py-2"
                        >
                            <option value="fixed">{{ currency_symbol() }}</option>
                            <option value="percentage">%</option>
                        </select>
                    </div>
                </div>
            @endif

            <!-- Totals -->
            @if(count($cartItems) > 0)
                <div class="border-t border-gray-200 pt-4 space-y-2 mb-4">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal:</span>
                        <span>{{ format_currency($subtotal) }}</span>
                    </div>

                    @if($totalDiscount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-{{ format_currency($totalDiscount) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-2xl font-bold text-gray-900 pt-2 border-t">
                        <span>Total:</span>
                        <span>{{ format_currency($grandTotal) }}</span>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button
                    wire:click="proceedToPayment"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg text-lg flex items-center justify-center space-x-2"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Proceed to Payment (F2)</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Customer Modal -->
    @if($showCustomerModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Select Customer</h3>
                    <button wire:click="$set('showCustomerModal', false)" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="customerSearchTerm"
                    placeholder="Search by name or phone..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4"
                >

                <div class="max-h-60 overflow-y-auto space-y-2">
                    @foreach($customers as $customer)
                        <button
                            wire:click="selectCustomer({{ $customer->id }})"
                            class="w-full text-left border border-gray-200 rounded-lg p-3 hover:bg-gray-50"
                        >
                            <p class="font-medium">{{ $customer->name }}</p>
                            <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Held Bills Modal -->
    @if($showHoldBillsModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Held Bills</h3>
                    <button wire:click="$set('showHoldBillsModal', false)" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if(count($heldBills) > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($heldBills as $bill)
                            <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $bill['customer_name'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $bill['item_count'] }} items • {{ format_currency($bill['total']) }}</p>
                                    <p class="text-xs text-gray-500">Held at {{ $bill['time'] }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        wire:click="retrieveBill('{{ $bill['id'] }}')"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                                    >
                                        Retrieve
                                    </button>
                                    <button
                                        wire:click="deleteHeldBill('{{ $bill['id'] }}')"
                                        wire:confirm="Delete this held bill?"
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-gray-500">No held bills</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Payment Modal Component -->
    @livewire('pos.payment-modal')
</div>

<script>
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // F2 - Proceed to payment
        if (e.key === 'F2') {
            e.preventDefault();
            @this.proceedToPayment();
        }

        // F3 - Hold bill
        if (e.key === 'F3') {
            e.preventDefault();
            @this.holdBill();
        }

        // ESC - Clear cart (with confirmation)
        if (e.key === 'Escape') {
            e.preventDefault();
            if (confirm('Clear cart?')) {
                @this.clearCart();
            }
        }
    });
</script>
