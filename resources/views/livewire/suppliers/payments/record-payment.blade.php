<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Record Supplier Payment</h1>
            <p class="mt-1 text-sm text-gray-600">Record a payment to a supplier and update outstanding balance</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Supplier -->
                    <div class="md:col-span-2">
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.live="supplier_id"
                            id="supplier_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('supplier_id') border-red-500 @enderror"
                        >
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->name }} (Outstanding: ₹{{ number_format($supplier->outstanding_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Current Outstanding -->
                    @if($selectedSupplier)
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800">Current Outstanding Balance</h4>
                                    <p class="mt-1 text-2xl font-bold text-blue-900">₹{{ number_format($selectedSupplier->outstanding_balance, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <h4 class="text-sm font-medium text-blue-800">Credit Terms</h4>
                                    <p class="mt-1 text-lg font-semibold text-blue-900">{{ $selectedSupplier->credit_terms }} days</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Date -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">
                            Payment Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="payment_date"
                            type="date"
                            id="payment_date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('payment_date') border-red-500 @enderror"
                        >
                        @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input
                                wire:model="amount"
                                type="number"
                                id="amount"
                                min="0"
                                step="0.01"
                                class="pl-7 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('amount') border-red-500 @enderror"
                            >
                        </div>
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Payment Mode -->
                    <div>
                        <label for="payment_mode" class="block text-sm font-medium text-gray-700">
                            Payment Mode <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="payment_mode"
                            id="payment_mode"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        @error('payment_mode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Bank Reference (shown only for bank transfer) -->
                    @if($payment_mode === 'bank_transfer')
                        <div>
                            <label for="bank_reference" class="block text-sm font-medium text-gray-700">
                                Bank Reference
                            </label>
                            <input
                                wire:model="bank_reference"
                                type="text"
                                id="bank_reference"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                            @error('bank_reference') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- Reference Number -->
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700">
                            Reference Number
                        </label>
                        <input
                            wire:model="reference_number"
                            type="text"
                            id="reference_number"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        @error('reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Notes
                        </label>
                        <textarea
                            wire:model="notes"
                            id="notes"
                            rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        ></textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a
                        href="{{ route('suppliers.payments.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
