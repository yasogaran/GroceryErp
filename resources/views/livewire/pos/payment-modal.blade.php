<div>
    @if($show)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-3xl">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Complete Payment</h2>

                <!-- Bill Amount Display -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 mb-6 text-center text-white shadow-lg">
                    <p class="text-sm mb-2 opacity-90">Total Bill Amount</p>
                    <p class="text-6xl font-bold">
                        Rs. {{ number_format($grandTotal, 2) }}
                    </p>
                </div>

                <!-- Customer Info (if selected) -->
                @if(isset($cartData['customer_id']) && $cartData['customer_id'])
                    @php
                        $customer = \App\Models\Customer::find($cartData['customer_id']);
                    @endphp
                    @if($customer)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-semibold text-green-800">Customer: {{ $customer->name }}</span>
                                @if($customer->phone)
                                    <span class="ml-2 text-sm text-green-600">{{ $customer->phone }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Amount Received Input -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-700 mb-3">Amount Received from Customer</label>
                    <input
                        type="number"
                        wire:model.live="paidAmount"
                        class="w-full text-4xl text-center font-bold border-2 border-gray-300 rounded-lg px-6 py-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        autofocus
                        id="paid-amount-input"
                    >
                    @error('paidAmount')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-2 mt-4">
                        @php
                            $quickAmounts = [100, 500, 1000, 5000];
                        @endphp
                        @foreach($quickAmounts as $amount)
                            <button
                                wire:click="$set('paidAmount', {{ $amount }})"
                                class="bg-gray-200 hover:bg-gray-300 py-3 rounded-lg font-medium">
                                Rs. {{ $amount }}
                            </button>
                        @endforeach
                        <button
                            wire:click="$set('paidAmount', {{ $grandTotal }})"
                            class="col-span-2 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-bold">
                            Exact Amount (Rs. {{ number_format($grandTotal, 2) }})
                        </button>
                    </div>
                </div>

                <!-- Full Credit Invoice Button (when amount is 0) -->
                @if($paidAmount == 0 && isset($cartData['customer_id']) && $cartData['customer_id'])
                    <div class="mb-6">
                        <div class="bg-orange-50 border-2 border-orange-500 rounded-lg p-6 text-center">
                            <p class="text-orange-800 font-bold text-lg mb-3">💳 Full Credit Invoice Option</p>
                            <p class="text-sm text-orange-700 mb-4">
                                Customer will pay the full amount later. No payment received now.
                            </p>
                            <button
                                wire:click="markAsFullCredit"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-bold"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="markAsFullCredit">
                                    Mark as Full Credit Invoice (Rs. {{ number_format($grandTotal, 2) }})
                                </span>
                                <span wire:loading wire:target="markAsFullCredit">Processing...</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Change/Balance Display -->
                @if($paidAmount > 0)
                    <div class="mb-6">
                        @if($changeToReturn > 0)
                            <!-- Show change to return -->
                            <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6 text-center">
                                <p class="text-sm text-green-700 mb-2">CHANGE TO RETURN TO CUSTOMER</p>
                                <p class="text-5xl font-bold text-green-600">
                                    Rs. {{ number_format($changeToReturn, 2) }}
                                </p>
                            </div>
                        @elseif($isCreditInvoice)
                            <!-- Show credit invoice warning -->
                            <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-6">
                                <div class="flex items-start">
                                    <svg class="w-8 h-8 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-lg font-bold text-yellow-800 mb-2">CREDIT INVOICE</p>
                                        <p class="text-sm text-yellow-700 mb-3">
                                            Amount received is less than the bill amount. This will be recorded as a credit invoice.
                                        </p>
                                        <div class="bg-white rounded p-3 mb-2">
                                            <div class="flex justify-between mb-1">
                                                <span class="text-gray-600">Bill Amount:</span>
                                                <span class="font-semibold">Rs. {{ number_format($grandTotal, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between mb-1">
                                                <span class="text-gray-600">Amount Received:</span>
                                                <span class="font-semibold">Rs. {{ number_format($paidAmount, 2) }}</span>
                                            </div>
                                            <div class="border-t pt-1 mt-1">
                                                <div class="flex justify-between">
                                                    <span class="font-bold text-red-600">Balance Due:</span>
                                                    <span class="font-bold text-red-600">Rs. {{ number_format($grandTotal - $paidAmount, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if(!isset($cartData['customer_id']) || !$cartData['customer_id'])
                                            <div class="bg-red-100 border border-red-300 rounded p-3 mt-2">
                                                <p class="text-red-700 font-semibold">⚠ Customer Required</p>
                                                <p class="text-sm text-red-600">Please select a customer before creating a credit invoice.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                <p class="text-blue-700">Exact payment - No change required</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Error Messages -->
                @if(session()->has('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button
                        wire:click="closeModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-4 rounded-lg font-bold text-lg"
                        wire:loading.attr="disabled">
                        Cancel
                    </button>
                    <button
                        wire:click="processPaymentAmount"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        @disabled($paidAmount <= 0 || ($isCreditInvoice && (!isset($cartData['customer_id']) || !$cartData['customer_id'])))>
                        <span wire:loading.remove wire:target="processPaymentAmount">
                            Complete Payment
                            @if($changeToReturn > 0)
                                & Return Change
                            @endif
                        </span>
                        <span wire:loading wire:target="processPaymentAmount">Processing...</span>
                    </button>
                </div>

                <!-- Keyboard Shortcuts Info -->
                <div class="mt-4 text-center text-xs text-gray-500">
                    Press Enter to submit | ESC to cancel
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <script>
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.getElementById('paid-amount-input') === document.activeElement) {
                    @this.processPaymentAmount();
                }
                if (e.key === 'Escape') {
                    @this.closeModal();
                }
            });

            // Auto-focus when modal opens
            setTimeout(() => {
                document.getElementById('paid-amount-input')?.focus();
            }, 100);
        </script>
    @endif
</div>
