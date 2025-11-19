<div class="py-4">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header - Smaller Size -->
        <div class="mb-4">
            <h1 class="text-lg font-semibold text-gray-900">
                {{ $isEditMode ? 'Edit GRN' : 'Create New GRN' }}
            </h1>
            <p class="mt-0.5 text-xs text-gray-600">
                {{ $isEditMode ? 'Update goods receipt note' : 'Record goods received from supplier' }}
            </p>
        </div>

        <form wire:submit.prevent="save">
            <!-- GRN Header Information -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">GRN Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- GRN Number -->
                    <div>
                        <label for="grn_number" class="block text-xs font-medium text-gray-700 mb-1">
                            GRN Number
                        </label>
                        <input
                            type="text"
                            id="grn_number"
                            value="{{ $grn_number }}"
                            disabled
                            class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 bg-gray-100 text-sm"
                        >
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label for="supplier_id" class="block text-xs font-medium text-gray-700 mb-1">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="supplier_id"
                            id="supplier_id"
                            class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm @error('supplier_id') border-red-500 @enderror"
                        >
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- GRN Date -->
                    <div>
                        <label for="grn_date" class="block text-xs font-medium text-gray-700 mb-1">
                            GRN Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="grn_date"
                            type="date"
                            id="grn_date"
                            class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm @error('grn_date') border-red-500 @enderror"
                        >
                        @error('grn_date') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">
                            Notes
                        </label>
                        <input
                            wire:model="notes"
                            type="text"
                            id="notes"
                            class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                    </div>
                </div>
            </div>

            <!-- Main Content: Form on Left, Items List on Right -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Left Side: Add Item Form -->
                <div class="bg-white rounded-lg shadow-sm p-4 h-fit">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-medium text-gray-900">
                            {{ $editingItemIndex !== null ? 'Edit Item' : 'Add Items' }}
                        </h3>
                        @if($editingItemIndex !== null)
                            <button
                                wire:click.prevent="cancelEdit"
                                type="button"
                                class="text-xs text-gray-600 hover:text-gray-900"
                            >
                                Cancel Edit
                            </button>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <!-- Product -->
                        <div>
                            <label for="product_id" class="block text-xs font-medium text-gray-700 mb-1">
                                Product <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model.live="product_id"
                                id="product_id"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Boxes and Pieces -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="received_boxes" class="block text-xs font-medium text-gray-700 mb-1">
                                    Boxes
                                </label>
                                <input
                                    wire:model.live="received_boxes"
                                    type="number"
                                    id="received_boxes"
                                    min="0"
                                    step="1"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    {{ $selectedProduct && $selectedProduct->has_packaging ? '' : 'disabled' }}
                                >
                                @if($selectedProduct && $selectedProduct->has_packaging)
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $selectedProduct->packaging()->first()->pieces_per_package ?? 0 }} pcs/box
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label for="received_pieces" class="block text-xs font-medium text-gray-700 mb-1">
                                    Pieces <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="received_pieces"
                                    type="number"
                                    id="received_pieces"
                                    min="0"
                                    step="0.01"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 bg-gray-100 text-sm"
                                    {{ $selectedProduct && $selectedProduct->has_packaging ? 'readonly' : '' }}
                                >
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label for="unit_price" class="block text-xs font-medium text-gray-700 mb-1">
                                    Unit Cost <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="unit_price"
                                    type="number"
                                    id="unit_price"
                                    min="0"
                                    step="0.01"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>

                            <div>
                                <label for="min_selling_price" class="block text-xs font-medium text-gray-700 mb-1">
                                    Min SP <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="min_selling_price"
                                    type="number"
                                    id="min_selling_price"
                                    min="0"
                                    step="0.01"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>

                            <div>
                                <label for="max_selling_price" class="block text-xs font-medium text-gray-700 mb-1">
                                    MRP <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="max_selling_price"
                                    type="number"
                                    id="max_selling_price"
                                    min="0"
                                    step="0.01"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>
                        </div>

                        <!-- Batch and Dates -->
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label for="batch_number" class="block text-xs font-medium text-gray-700 mb-1">
                                    Batch Number
                                </label>
                                <input
                                    wire:model="batch_number"
                                    type="text"
                                    id="batch_number"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>

                            <div>
                                <label for="manufacturing_date" class="block text-xs font-medium text-gray-700 mb-1">
                                    Mfg Date
                                </label>
                                <input
                                    wire:model="manufacturing_date"
                                    type="date"
                                    id="manufacturing_date"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>

                            <div>
                                <label for="expiry_date" class="block text-xs font-medium text-gray-700 mb-1">
                                    Expiry Date
                                </label>
                                <input
                                    wire:model="expiry_date"
                                    type="date"
                                    id="expiry_date"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-1.5 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>
                        </div>

                        <!-- Add/Update Button -->
                        <div class="pt-2">
                            <button
                                wire:click.prevent="addItem"
                                type="button"
                                class="w-full px-4 py-2 {{ $editingItemIndex !== null ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $editingItemIndex !== null ? 'focus:ring-blue-500' : 'focus:ring-green-500' }} text-sm font-medium"
                            >
                                {{ $editingItemIndex !== null ? 'Update Item' : 'Add Item' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Items List -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-medium text-gray-900">Items Added ({{ count($items) }})</h3>
                    </div>

                    @if(count($items) > 0)
                        <div class="overflow-y-auto" style="max-height: 600px;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th scope="col" class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th scope="col" class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                                        <th scope="col" class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($items as $index => $item)
                                        <tr class="{{ $editingItemIndex === $index ? 'bg-blue-50' : '' }}">
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</div>
                                                @if($item['batch_number'] || $item['expiry_date'])
                                                    <div class="text-xs text-gray-500">
                                                        @if($item['batch_number'])
                                                            B:{{ $item['batch_number'] }}
                                                        @endif
                                                        @if($item['expiry_date'])
                                                            | Exp:{{ \Carbon\Carbon::parse($item['expiry_date'])->format('m/y') }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right text-gray-900">
                                                @if($item['received_boxes'] > 0)
                                                    <span class="text-xs text-gray-500">{{ $item['received_boxes'] }}b</span>
                                                @endif
                                                {{ number_format($item['received_pieces'], 0) }}
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right text-gray-900">{{ settings('currency_symbol', 'Rs.') }} {{ number_format($item['unit_price'], 2) }}</td>
                                            <td class="px-2 py-2 text-sm text-right font-medium text-gray-900">{{ settings('currency_symbol', 'Rs.') }} {{ number_format($item['total_amount'], 2) }}</td>
                                            <td class="px-2 py-2 text-center text-sm">
                                                <div class="flex justify-center gap-2">
                                                    <button
                                                        wire:click.prevent="editItem({{ $index }})"
                                                        type="button"
                                                        class="text-blue-600 hover:text-blue-900 font-medium"
                                                        title="Edit"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        wire:click.prevent="removeItem({{ $index }})"
                                                        type="button"
                                                        class="text-red-600 hover:text-red-900 font-medium"
                                                        title="Remove"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 sticky bottom-0">
                                    <tr class="font-semibold">
                                        <td colspan="3" class="px-3 py-2 text-right text-sm text-gray-900">Grand Total:</td>
                                        <td class="px-2 py-2 text-sm text-right text-gray-900">{{ settings('currency_symbol', 'Rs.') }} {{ number_format(array_sum(array_column($items, 'total_amount')), 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">No items added yet</p>
                            <p class="text-xs text-gray-500">Add items using the form on the left</p>
                        </div>
                    @endif
                </div>
            </div>

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
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ count($items) === 0 ? 'disabled' : '' }}
                >
                    {{ $isEditMode ? 'Update GRN' : 'Create GRN' }}
                </button>
            </div>
        </form>
    </div>
</div>
