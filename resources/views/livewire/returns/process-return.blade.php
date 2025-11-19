<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Process Sale Return</h1>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center">
                <div class="flex items-center {{ $step >= 1 ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="rounded-full h-10 w-10 flex items-center justify-center border-2 {{ $step >= 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300' }}">
                        1
                    </div>
                    <span class="ml-2 font-medium">Search Invoice</span>
                </div>

                <div class="w-24 h-1 mx-4 {{ $step >= 2 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

                <div class="flex items-center {{ $step >= 2 ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="rounded-full h-10 w-10 flex items-center justify-center border-2 {{ $step >= 2 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300' }}">
                        2
                    </div>
                    <span class="ml-2 font-medium">Select Items</span>
                </div>

                <div class="w-24 h-1 mx-4 {{ $step >= 3 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

                <div class="flex items-center {{ $step >= 3 ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="rounded-full h-10 w-10 flex items-center justify-center border-2 {{ $step >= 3 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300' }}">
                        3
                    </div>
                    <span class="ml-2 font-medium">Process Refund</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: Search Invoice -->
    @if($step === 1)
        <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
            <h2 class="text-xl font-bold mb-4">Search Original Invoice</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Enter Invoice Number
                </label>
                <input
                    type="text"
                    wire:model="invoiceSearch"
                    wire:keydown.enter="searchSale"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg"
                    placeholder="INV-YYYYMMDD-XXXX or scan barcode"
                    autofocus
                >
                @error('invoiceSearch')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                wire:click="searchSale"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg">
                Search Invoice
            </button>
        </div>
    @endif

    <!-- Step 2: Select Items -->
    @if($step === 2 && $selectedSale)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- Original Sale Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Invoice Number</p>
                        <p class="font-medium">{{ $selectedSale->invoice_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Sale Date</p>
                        <p class="font-medium">{{ $selectedSale->sale_date->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-medium">{{ $selectedSale->customer?->name ?? 'Walk-in' }}</p>
                    </div>
                </div>
            </div>

            <!-- Return Items Selection -->
            <h2 class="text-xl font-bold mb-4">Select Items to Return</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4 w-12">
                                Select
                            </th>
                            <th class="text-left py-3 px-4">Product</th>
                            <th class="text-right py-3 px-4">Sold Qty</th>
                            <th class="text-right py-3 px-4">Returned</th>
                            <th class="text-right py-3 px-4">Remaining</th>
                            <th class="text-right py-3 px-4">Return Qty</th>
                            <th class="text-center py-3 px-4">Damaged?</th>
                            <th class="text-right py-3 px-4">Refund Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnItems as $index => $item)
                            <tr class="border-b">
                                <td class="py-3 px-4">
                                    <input
                                        type="checkbox"
                                        wire:model.live="returnItems.{{ $index }}.selected"
                                        class="form-checkbox h-5 w-5 text-blue-600"
                                    >
                                </td>
                                <td class="py-3 px-4">{{ $item['product_name'] }}</td>
                                <td class="py-3 px-4 text-right">{{ number_format($item['original_quantity'], 0) }}</td>
                                <td class="py-3 px-4 text-right text-red-600">{{ number_format($item['already_returned'], 0) }}</td>
                                <td class="py-3 px-4 text-right font-medium">{{ number_format($item['remaining_quantity'], 0) }}</td>
                                <td class="py-3 px-4">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.300ms="returnItems.{{ $index }}.quantity"
                                        class="w-24 border border-gray-300 rounded px-2 py-1 text-right"
                                        min="0"
                                        max="{{ $item['remaining_quantity'] }}"
                                        step="1"
                                        @disabled(!$item['selected'])
                                    >
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <input
                                        type="checkbox"
                                        wire:model="returnItems.{{ $index }}.is_damaged"
                                        class="form-checkbox h-5 w-5 text-red-600"
                                        @disabled(!$item['selected'])
                                    >
                                </td>
                                <td class="py-3 px-4 text-right font-medium">
                                    Rs. {{ number_format($item['refund_amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="7" class="py-3 px-4 text-right font-bold">Total Refund:</td>
                            <td class="py-3 px-4 text-right font-bold text-lg text-blue-600">
                                Rs. {{ number_format($totalRefund, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex space-x-4 mt-6">
                <button
                    wire:click="backToSearch"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    Back
                </button>
                <button
                    wire:click="proceedToRefund"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Proceed to Refund
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Process Refund -->
    @if($step === 3)
        <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
            <h2 class="text-xl font-bold mb-6">Process Refund</h2>

            <!-- Refund Summary -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-center">
                <p class="text-sm text-gray-600 mb-2">Total Refund Amount</p>
                <p class="text-4xl font-bold text-blue-600">Rs. {{ number_format($totalRefund, 2) }}</p>
            </div>

            <!-- Refund Mode -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Refund Mode <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="refundMode"
                            value="cash"
                            class="form-radio h-5 w-5 text-blue-600"
                        >
                        <span class="ml-2">Cash</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="refundMode"
                            value="bank_transfer"
                            class="form-radio h-5 w-5 text-blue-600"
                        >
                        <span class="ml-2">Bank Transfer</span>
                    </label>
                </div>
                @error('refundMode')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bank Account (if bank transfer) -->
            @if($refundMode === 'bank_transfer')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bank Account <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="bankAccountId"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 @error('bankAccountId') border-red-500 @enderror">
                        <option value="">Select Bank Account</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->account_name }} ({{ $account->account_code }}) - Balance: ₹{{ number_format($account->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('bankAccountId')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Deduct from Shift Checkbox (shown only for cash and if active shift exists) -->
            @if($refundMode === 'cash' && $activeShift)
                <div class="mb-4">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <label class="flex items-start cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="deductFromShift"
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <span class="ml-3">
                                <span class="block text-sm font-medium text-amber-900">
                                    Deduct this refund from my current shift cash
                                </span>
                                <span class="block text-xs text-amber-700 mt-1">
                                    Active Shift: Started at {{ $activeShift->shift_start->format('d M Y, h:i A') }} |
                                    Current Cash: {{ settings('currency_symbol', 'Rs.') }} {{ number_format($activeShift->total_cash_sales, 2) }}
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
            @endif

            <!-- Return Reason -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Return Reason <span class="text-red-500">*</span>
                </label>
                <textarea
                    wire:model="returnReason"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Enter reason for return..."
                ></textarea>
                @error('returnReason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Items Summary -->
            <div class="border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="font-medium mb-3">Returning Items:</h3>
                <ul class="space-y-2">
                    @foreach(collect($returnItems)->where('selected', true) as $item)
                        <li class="flex justify-between text-sm">
                            <span>
                                {{ $item['product_name'] }} × {{ number_format($item['quantity'], 0) }}
                                @if($item['is_damaged'])
                                    <span class="text-red-600 font-medium">(Damaged)</span>
                                @endif
                            </span>
                            <span class="font-medium">Rs. {{ number_format($item['refund_amount'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4">
                <button
                    wire:click="backToItems"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    Back
                </button>
                <button
                    wire:click="processReturn"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm & Process Return</span>
                    <span wire:loading>Processing...</span>
                </button>
            </div>
        </div>
    @endif
</div>
