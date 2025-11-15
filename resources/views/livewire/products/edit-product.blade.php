<form wire:submit="update">
    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button
                type="button"
                wire:click="setActiveTab('basic')"
                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Basic Info
            </button>
            <button
                type="button"
                wire:click="setActiveTab('pricing')"
                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'pricing' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Pricing
            </button>
            <button
                type="button"
                wire:click="setActiveTab('packaging')"
                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'packaging' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Packaging
            </button>
            <button
                type="button"
                wire:click="setActiveTab('image')"
                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'image' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Image
            </button>
            <button
                type="button"
                wire:click="setActiveTab('stock')"
                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'stock' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Stock Info
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="space-y-4">
        <!-- Basic Info Tab -->
        <div class="{{ $activeTab === 'basic' ? '' : 'hidden' }}">
            <div class="space-y-4">
                <!-- SKU -->
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                    <input
                        wire:model="sku"
                        type="text"
                        id="sku"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="e.g., PRD-12345678"
                    >
                    @error('sku') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Barcode -->
                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input
                            wire:model="barcode"
                            type="text"
                            id="barcode"
                            class="flex-1 block w-full px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="e.g., 2012345678901"
                        >
                        <button
                            type="button"
                            wire:click="generateBarcode"
                            class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100"
                        >
                            Generate
                        </button>
                    </div>
                    @error('barcode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name <span class="text-red-500">*</span></label>
                    <input
                        wire:model="name"
                        type="text"
                        id="name"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="e.g., Coca Cola 330ml"
                    >
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select
                        wire:model="category_id"
                        id="category_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                    <input
                        wire:model="brand"
                        type="text"
                        id="brand"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="e.g., Coca Cola"
                    >
                    @error('brand') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Base Unit -->
                <div>
                    <label for="base_unit" class="block text-sm font-medium text-gray-700">Base Unit <span class="text-red-500">*</span></label>
                    <select
                        wire:model="base_unit"
                        id="base_unit"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        @foreach($baseUnits as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('base_unit') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        wire:model="description"
                        id="description"
                        rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Enter product description (optional)"
                    ></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input
                        wire:model="is_active"
                        type="checkbox"
                        id="is_active"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <!-- Pricing Tab -->
        <div class="{{ $activeTab === 'pricing' ? '' : 'hidden' }}">
            <div class="space-y-4">
                <!-- Min Selling Price -->
                <div>
                    <label for="min_selling_price" class="block text-sm font-medium text-gray-700">Minimum Selling Price <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input
                            wire:model="min_selling_price"
                            type="number"
                            step="0.01"
                            id="min_selling_price"
                            class="block w-full pl-7 pr-12 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="0.00"
                        >
                    </div>
                    @error('min_selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Max Selling Price (MRP) -->
                <div>
                    <label for="max_selling_price" class="block text-sm font-medium text-gray-700">Maximum Selling Price (MRP) <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input
                            wire:model="max_selling_price"
                            type="number"
                            step="0.01"
                            id="max_selling_price"
                            class="block w-full pl-7 pr-12 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="0.00"
                        >
                    </div>
                    @error('max_selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Reorder Level -->
                <div>
                    <label for="reorder_level" class="block text-sm font-medium text-gray-700">Reorder Level <span class="text-red-500">*</span></label>
                    <input
                        wire:model="reorder_level"
                        type="number"
                        step="0.01"
                        id="reorder_level"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="0.00"
                    >
                    <p class="mt-1 text-xs text-gray-500">Stock level at which to reorder this product.</p>
                    @error('reorder_level') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Packaging Tab -->
        <div class="{{ $activeTab === 'packaging' ? '' : 'hidden' }}">
            <div class="space-y-4">
                <!-- Has Packaging Toggle -->
                <div class="flex items-center">
                    <input
                        wire:model.live="has_packaging"
                        type="checkbox"
                        id="has_packaging"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="has_packaging" class="ml-2 block text-sm text-gray-900">
                        This product has packaging options (box, carton, etc.)
                    </label>
                </div>

                @if($has_packaging)
                    <div class="border-t border-gray-200 pt-4 space-y-4">
                        <!-- Packaging Name -->
                        <div>
                            <label for="packaging_name" class="block text-sm font-medium text-gray-700">Packaging Name <span class="text-red-500">*</span></label>
                            <input
                                wire:model="packaging_name"
                                type="text"
                                id="packaging_name"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Box, Carton, Case"
                            >
                            @error('packaging_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Pieces per Package -->
                        <div>
                            <label for="pieces_per_package" class="block text-sm font-medium text-gray-700">Pieces per Package <span class="text-red-500">*</span></label>
                            <input
                                wire:model="pieces_per_package"
                                type="number"
                                id="pieces_per_package"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., 24"
                            >
                            @error('pieces_per_package') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Package Barcode -->
                        <div>
                            <label for="package_barcode" class="block text-sm font-medium text-gray-700">Package Barcode</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input
                                    wire:model="package_barcode"
                                    type="text"
                                    id="package_barcode"
                                    class="flex-1 block w-full px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="e.g., 2112345678901"
                                >
                                <button
                                    type="button"
                                    wire:click="generatePackageBarcode"
                                    class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100"
                                >
                                    Generate
                                </button>
                            </div>
                            @error('package_barcode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Discount Type -->
                        <div>
                            <label for="discount_type" class="block text-sm font-medium text-gray-700">Discount Type <span class="text-red-500">*</span></label>
                            <select
                                wire:model="discount_type"
                                id="discount_type"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                            @error('discount_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Discount Value -->
                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-gray-700">Discount Value <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input
                                    wire:model="discount_value"
                                    type="number"
                                    step="0.01"
                                    id="discount_value"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="0.00"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $discount_type === 'percentage' ? '%' : '$' }}</span>
                                </div>
                            </div>
                            @error('discount_value') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Image Tab -->
        <div class="{{ $activeTab === 'image' ? '' : 'hidden' }}">
            <div class="space-y-4">
                <!-- Current Image Preview -->
                @if($existing_image_path)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                        <img src="{{ Storage::url($existing_image_path) }}" class="h-32 w-32 object-cover rounded border">
                    </div>
                @endif

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $existing_image_path ? 'Replace Image' : 'Product Image' }}</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            @if($image)
                                <div class="mb-4">
                                    <img src="{{ $image->temporaryUrl() }}" class="mx-auto h-32 w-32 object-cover rounded">
                                </div>
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                            <div class="flex text-sm text-gray-600">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input wire:model="image" id="image" name="image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    @error('image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Stock Info Tab (Read-only) -->
        <div class="{{ $activeTab === 'stock' ? '' : 'hidden' }}">
            <div class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Stock levels are managed via GRN (Goods Receipt Note) and POS transactions.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Current Stock -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Stock Quantity</label>
                    <div class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 sm:text-sm">
                        {{ number_format($current_stock_quantity, 2) }} {{ $base_unit }}
                    </div>
                </div>

                <!-- Damaged Stock -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Damaged Stock Quantity</label>
                    <div class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 sm:text-sm">
                        {{ number_format($damaged_stock_quantity, 2) }} {{ $base_unit }}
                    </div>
                </div>

                <!-- Reorder Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reorder Status</label>
                    <div class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 sm:text-sm">
                        @if($current_stock_quantity <= $reorder_level)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Below Reorder Level - Restock Needed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Stock Level OK
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 pt-4">
        <button
            type="button"
            wire:click="$dispatch('close-modal')"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Cancel
        </button>
        <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Update Product
        </button>
    </div>
</form>
