<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Close Shift</h1>
            <p class="mt-1 text-sm text-gray-600">Review your shift and count the cash to close.</p>
        </div>

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Shift Summary -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Shift Summary</h3>
            </div>

            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Shift Started</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $shift->shift_start->format('g:i A') }}</p>
                        <p class="text-xs text-gray-500">{{ $shift->shift_start->format('M d, Y') }}</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Opening Cash</p>
                        <p class="text-lg font-semibold text-gray-900">{{ format_currency($shift->opening_cash) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $shift->total_transactions }}</p>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600">Total Cash Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ format_currency($shift->total_cash_sales) }}</p>
                    </div>

                    <div class="bg-indigo-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600">Total Bank Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ format_currency($shift->total_bank_sales) }}</p>
                    </div>
                </div>

                <div class="bg-blue-100 p-4 rounded-lg border-2 border-blue-300">
                    <p class="text-sm text-gray-700 font-medium">Expected Cash in Drawer</p>
                    <p class="text-3xl font-bold text-blue-900">{{ format_currency($expectedCash) }}</p>
                    <p class="text-xs text-gray-600 mt-1">Opening Cash + Cash Sales</p>
                </div>
            </div>

            <!-- Cash Count Form -->
            <form wire:submit="closeShift" class="px-6 py-4 bg-gray-50 border-t border-gray-200 space-y-4">
                <div>
                    <label for="closingCash" class="block text-sm font-medium text-gray-700 mb-2">
                        Actual Cash Counted (Rs.) <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.live="closingCash"
                        type="number"
                        step="0.01"
                        min="0"
                        id="closingCash"
                        class="appearance-none relative block w-full px-4 py-4 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-2xl text-center font-semibold"
                        placeholder="0.00"
                    >
                    @error('closingCash')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($closingCash > 0)
                    <div class="p-4 rounded-lg {{ $calculatedVariance < 0 ? 'bg-red-100 border border-red-300' : ($calculatedVariance > 0 ? 'bg-green-100 border border-green-300' : 'bg-gray-100 border border-gray-300') }}">
                        <p class="text-sm font-medium {{ $calculatedVariance < 0 ? 'text-red-700' : ($calculatedVariance > 0 ? 'text-green-700' : 'text-gray-700') }}">
                            Variance
                        </p>
                        <p class="text-2xl font-bold {{ $calculatedVariance < 0 ? 'text-red-900' : ($calculatedVariance > 0 ? 'text-green-900' : 'text-gray-900') }}">
                            {{ $calculatedVariance >= 0 ? '+' : '' }}{{ format_currency($calculatedVariance) }}
                        </p>
                        <p class="text-xs mt-1 {{ $calculatedVariance < 0 ? 'text-red-600' : ($calculatedVariance > 0 ? 'text-green-600' : 'text-gray-600') }}">
                            @if($calculatedVariance < 0)
                                Cash is short
                            @elseif($calculatedVariance > 0)
                                Cash is over
                            @else
                                Cash count matches expected
                            @endif
                        </p>
                    </div>
                @endif

                @if($calculatedVariance != 0 && $closingCash > 0)
                    <div>
                        <label for="varianceNotes" class="block text-sm font-medium text-gray-700 mb-2">
                            Variance Notes (Optional)
                        </label>
                        <textarea
                            wire:model="varianceNotes"
                            id="varianceNotes"
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Explain the variance if any..."
                        ></textarea>
                    </div>
                @endif

                <div class="flex items-center">
                    <input
                        wire:model="verified"
                        id="verified"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="verified" class="ml-2 block text-sm text-gray-900">
                        I verify that the cash count is correct and accurate
                    </label>
                </div>
                @error('verified')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @error('general')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end space-x-3 pt-4">
                    <a
                        href="{{ route('pos.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Close Shift & Logout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
