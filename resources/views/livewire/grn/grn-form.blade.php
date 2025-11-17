<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ $isEditMode ? 'Edit GRN' : 'Create New GRN' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $isEditMode ? 'Update goods receipt note' : 'Record goods received from supplier' }}
            </p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('item_success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('item_success') }}</span>
            </div>
        @endif

        @if (session()->has('item_error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('item_error') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="save">
            <!-- GRN Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">GRN Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- GRN Number -->
                    <div>
                        <label for="grn_number" class="block text-sm font-medium text-gray-700">
                            GRN Number
                        </label>
                        <input
                            type="text"
                            id="grn_number"
                            value="{{ $grn_number }}"
                            disabled
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 sm:text-sm"
                        >
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="supplier_id"
                            id="supplier_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('supplier_id') border-red-500 @enderror"
                        >
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- GRN Date -->
                    <div>
                        <label for="grn_date" class="block text-sm font-medium text-gray-700">
                            GRN Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="grn_date"
                            type="date"
                            id="grn_date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('grn_date') border-red-500 @enderror"
                        >
                        @error('grn_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-3">
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Notes
                        </label>
                        <textarea
                            wire:model="notes"
                            id="notes"
                            rows="2"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Add Item Form -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Items</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Product -->
                    <div class="lg:col-span-2">
                        <label for="product_id" class="block text-sm font-medium text-gray-700">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.live="product_id"
                            id="product_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Boxes -->
                    <div>
                        <label for="received_boxes" class="block text-sm font-medium text-gray-700">
                            Boxes
                        </label>
                        <input
                            wire:model.live="received_boxes"
                            type="number"
                            id="received_boxes"
                            min="0"
                            step="1"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            {{ $selectedProduct && $selectedProduct->has_packaging ? '' : 'disabled' }}
                        >
                        @if($selectedProduct && $selectedProduct->has_packaging)
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $selectedProduct->packaging()->first()->pieces_per_package ?? 0 }} pieces/box
                            </p>
                        @endif
                    </div>

                    <!-- Pieces -->
                    <div>
                        <label for="received_pieces" class="block text-sm font-medium text-gray-700">
                            Pieces <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="received_pieces"
                            type="number"
                            id="received_pieces"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 sm:text-sm"
                            {{ $selectedProduct && $selectedProduct->has_packaging ? 'readonly' : '' }}
                        >
                    </div>

                    <!-- Unit Price -->
                    <div>
                        <label for="unit_price" class="block text-sm font-medium text-gray-700">
                            Unit Cost <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="unit_price"
                            type="number"
                            id="unit_price"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        <p class="mt-1 text-xs text-gray-500">Purchase price per piece</p>
                    </div>

                    <!-- Min Selling Price -->
                    <div>
                        <label for="min_selling_price" class="block text-sm font-medium text-gray-700">
                            Min Selling Price <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="min_selling_price"
                            type="number"
                            id="min_selling_price"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        <p class="mt-1 text-xs text-gray-500">Minimum retail price</p>
                    </div>

                    <!-- Max Selling Price -->
                    <div>
                        <label for="max_selling_price" class="block text-sm font-medium text-gray-700">
                            Max Selling Price (MRP) <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="max_selling_price"
                            type="number"
                            id="max_selling_price"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        <p class="mt-1 text-xs text-gray-500">Maximum retail price</p>
                    </div>

                    <!-- Batch Number -->
                    <div>
                        <label for="batch_number" class="block text-sm font-medium text-gray-700">
                            Batch Number
                        </label>
                        <input
                            wire:model="batch_number"
                            type="text"
                            id="batch_number"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>

                    <!-- Manufacturing Date -->
                    <div>
                        <label for="manufacturing_date" class="block text-sm font-medium text-gray-700">
                            Mfg Date
                        </label>
                        <input
                            wire:model="manufacturing_date"
                            type="date"
                            id="manufacturing_date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700">
                            Expiry Date
                        </label>
                        <input
                            wire:model="expiry_date"
                            type="date"
                            id="expiry_date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>

                    <!-- Add Button -->
                    <div class="flex items-end">
                        <button
                            wire:click.prevent="addItem"
                            type="button"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Add Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            @if(count($items) > 0)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Items ({{ count($items) }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Boxes</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pieces</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min SP</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Max SP (MRP)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch/Expiry</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $index => $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item['product_name'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $item['received_boxes'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($item['received_pieces'], 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ format_currency($item['unit_price']) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ format_currency($item['min_selling_price'] ?? 0) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ format_currency($item['max_selling_price'] ?? 0) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ format_currency($item['total_amount']) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if($item['batch_number'])
                                                <div>Batch: {{ $item['batch_number'] }}</div>
                                            @endif
                                            @if($item['expiry_date'])
                                                <div>Exp: {{ $item['expiry_date'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <button
                                                wire:click.prevent="removeItem({{ $index }})"
                                                type="button"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="6" class="px-6 py-4 text-right text-sm text-gray-900">Grand Total:</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ format_currency(array_sum(array_column($items, 'total_amount'))) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-6">
                    Please add at least one item to the GRN.
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <a
                    href="{{ route('grn.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    {{ count($items) === 0 ? 'disabled' : '' }}
                >
                    {{ $isEditMode ? 'Update GRN' : 'Create GRN' }}
                </button>
            </div>
        </form>
    </div>
</div>
