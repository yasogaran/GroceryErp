<div>
    @if($show)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-6xl max-h-screen overflow-y-auto">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Payment</h2>

                <div class="grid grid-cols-2 gap-8">
                    <!-- Left: Payment Summary -->
                    <div>
                        <div class="bg-blue-50 rounded-lg p-6 mb-6 text-center">
                            <p class="text-sm text-gray-600 mb-2">Total Amount</p>
                            <p class="text-5xl font-bold text-blue-600">
                                {{ format_currency($grandTotal) }}
                            </p>
                        </div>

                        <div class="bg-green-50 rounded-lg p-6 mb-6 text-center">
                            <p class="text-sm text-gray-600 mb-2">Total Paid</p>
                            <p class="text-4xl font-bold text-green-600">
                                {{ format_currency($totalPaid) }}
                            </p>
                        </div>

                        <div class="bg-orange-50 rounded-lg p-6 text-center">
                            <p class="text-sm text-gray-600 mb-2">Remaining</p>
                            <p class="text-4xl font-bold text-orange-600">
                                {{ format_currency($remainingAmount) }}
                            </p>
                        </div>

                        <!-- Payments List -->
                        @if(count($payments) > 0)
                            <div class="mt-6">
                                <h3 class="font-bold mb-3">Payments Added:</h3>
                                <div class="space-y-2">
                                    @foreach($payments as $index => $payment)
                                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                                            <div>
                                                <span class="font-medium">
                                                    {{ $payment['mode'] === 'cash' ? 'Cash' : 'Bank Transfer' }}
                                                </span>
                                                @if($payment['mode'] === 'bank_transfer')
                                                    <p class="text-sm text-gray-600">{{ $payment['bank_account_name'] }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold">{{ format_currency($payment['amount']) }}</span>
                                                <button
                                                    wire:click="removePayment({{ $index }})"
                                                    class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Add Payment -->
                    <div>
                        <h3 class="font-bold text-lg mb-4">Add Payment</h3>

                        <!-- Quick Pay Buttons -->
                        @if($remainingAmount > 0)
                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <button
                                    wire:click="quickPayFull('cash')"
                                    class="bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold">
                                    Pay Full (Cash)
                                </button>
                                <button
                                    wire:click="quickPayFull('bank_transfer')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-lg font-bold">
                                    Pay Full (Bank)
                                </button>
                            </div>

                            <div class="relative my-6">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-white text-gray-500">OR Split Payment</span>
                                </div>
                            </div>
                        @endif

                        <!-- Payment Mode Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        wire:model.live="currentPaymentMode"
                                        value="cash"
                                        class="form-radio"
                                    >
                                    <span class="ml-2">Cash</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        wire:model.live="currentPaymentMode"
                                        value="bank_transfer"
                                        class="form-radio"
                                    >
                                    <span class="ml-2">Bank Transfer</span>
                                </label>
                            </div>
                        </div>

                        <!-- Bank Account (if bank transfer) -->
                        @if($currentPaymentMode === 'bank_transfer')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account</label>
                                <select
                                    wire:model="currentBankAccount"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                @error('currentBankAccount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Amount Input -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input
                                type="number"
                                wire:model="currentAmount"
                                class="w-full text-2xl text-center border border-gray-300 rounded-lg px-4 py-3"
                                placeholder="0.00"
                                step="0.01"
                                max="{{ $remainingAmount }}"
                            >
                            @error('currentAmount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            @php
                                $quickAmounts = [100, 500, 1000];
                                $quickAmounts[] = $remainingAmount;
                            @endphp
                            @foreach($quickAmounts as $amount)
                                <button
                                    wire:click="$set('currentAmount', {{ min($amount, $remainingAmount) }})"
                                    class="bg-gray-200 hover:bg-gray-300 py-2 rounded font-medium text-sm">
                                    {{ $amount == $remainingAmount ? 'Rest' : currency_symbol() . ' ' . $amount }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Add Payment Button -->
                        @if($remainingAmount > 0)
                            <button
                                wire:click="addPayment"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold mb-4">
                                Add Payment
                            </button>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex space-x-4 mt-6">
                            <button
                                wire:click="closeModal"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-bold">
                                Cancel
                            </button>
                            <button
                                wire:click="confirmPayment"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                                @disabled($remainingAmount > 0)
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Confirm Payment</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        </div>

                        @if(session()->has('error'))
                            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
