<div>
    @if($show)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg p-6 sm:p-8 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4 sm:mb-6">Complete Payment</h2>

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
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center flex-1 min-w-0">
                                    <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <div class="min-w-0">
                                        <span class="font-semibold text-green-800 truncate block">Customer: {{ $customer->name }}</span>
                                        @if($customer->phone)
                                            <span class="text-sm text-green-600 block">{{ $customer->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button
                                        wire:click="openCustomerSelector"
                                        class="text-sm text-blue-600 hover:text-blue-800 underline whitespace-nowrap">
                                        Change
                                    </button>
                                    <button
                                        wire:click="clearCustomer"
                                        class="text-sm text-red-600 hover:text-red-800 underline whitespace-nowrap">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- No customer selected - show select button -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-gray-600">No customer selected (Walk-in customer)</span>
                            </div>
                            <button
                                wire:click="openCustomerSelector"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                                Select Customer
                            </button>
                        </div>
                    </div>
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

                <!-- Payment Mode Selection -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-700 mb-3">Payment Method</label>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <!-- Cash Option -->
                        <button
                            type="button"
                            wire:click="$set('selectedPaymentMode', 'cash')"
                            class="flex items-center justify-center p-4 rounded-lg border-2 transition-all {{ $selectedPaymentMode === 'cash' ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 {{ $selectedPaymentMode === 'cash' ? 'text-green-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="font-semibold {{ $selectedPaymentMode === 'cash' ? 'text-green-700' : 'text-gray-700' }}">
                                    Cash
                                </span>
                            </div>
                        </button>

                        <!-- Bank Transfer Option -->
                        <button
                            type="button"
                            wire:click="$set('selectedPaymentMode', 'bank_transfer')"
                            class="flex items-center justify-center p-4 rounded-lg border-2 transition-all {{ $selectedPaymentMode === 'bank_transfer' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 {{ $selectedPaymentMode === 'bank_transfer' ? 'text-blue-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <span class="font-semibold {{ $selectedPaymentMode === 'bank_transfer' ? 'text-blue-700' : 'text-gray-700' }}">
                                    Bank Transfer
                                </span>
                            </div>
                        </button>
                    </div>

                    <!-- Bank Account Selection (shown when bank transfer is selected) -->
                    @if($selectedPaymentMode === 'bank_transfer')
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank Account</label>
                            <select
                                wire:model="selectedBankAccountId"
                                class="w-full border-2 border-blue-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->account_name }} ({{ $account->account_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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

    <!-- Customer Selector Modal -->
    @if($showCustomerSelector)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" style="z-index: 60;">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        @if($showCreateCustomer)
                            Create New Customer
                        @else
                            Select Customer
                        @endif
                    </h3>
                    <button wire:click="closeCustomerSelector" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if($showCreateCustomer)
                    <!-- Customer Creation Form -->
                    <form wire:submit.prevent="createCustomer">
                        <div class="space-y-4">
                            <!-- Success/Error Messages -->
                            @if (session()->has('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif
                            @if (session()->has('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif

                            <!-- Name -->
                            <div>
                                <label for="newCustomerName" class="block text-sm font-medium text-gray-700">
                                    Customer Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="newCustomerName"
                                    type="text"
                                    id="newCustomerName"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter customer name"
                                    autofocus
                                >
                                @error('newCustomerName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="newCustomerPhone" class="block text-sm font-medium text-gray-700">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="newCustomerPhone"
                                    type="text"
                                    id="newCustomerPhone"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="07XXXXXXXX"
                                >
                                @error('newCustomerPhone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <p class="text-xs text-gray-500">
                                Customer code will be auto-generated upon creation.
                            </p>
                        </div>

                        <div class="mt-6 flex space-x-3">
                            <button
                                type="button"
                                wire:click="backToCustomerList"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg font-medium">
                                Back to List
                            </button>
                            <button
                                type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium">
                                Create Customer
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Search Input -->
                    <div class="mb-4">
                        <input
                            type="text"
                            wire:model.live="customerSearchTerm"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Search by name or phone number..."
                            autofocus
                        >
                    </div>

                    <!-- Customer List -->
                    <div class="space-y-2">
                        @forelse($customers as $customer)
                            <div
                                wire:click="selectCustomer({{ $customer->id }})"
                                class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $customer->name }}</p>
                                        @if($customer->phone)
                                            <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                                        @endif
                                    </div>
                                    @if($customer->email)
                                        <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="font-medium">No customers found</p>
                                @if($customerSearchTerm)
                                    <p class="text-sm mt-1">No results for "{{ $customerSearchTerm }}"</p>
                                @else
                                    <p class="text-sm mt-1">Start typing to search</p>
                                @endif
                                <button
                                    wire:click="openCreateCustomer"
                                    class="mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create New Customer
                                </button>
                            </div>
                        @endforelse

                        <!-- Create New Customer Button (always visible when there are results too) -->
                        @if($customers->count() > 0)
                            <div class="pt-4 border-t border-gray-200">
                                <button
                                    wire:click="openCreateCustomer"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium inline-flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create New Customer
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
