<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $isEdit ? 'Edit Offer' : 'Create New Offer' }}</h1>
    </div>

    @if(session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form wire:submit="save">
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Offer Name *</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                            placeholder="e.g., Summer Sale 2025"
                        >
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <input
                            type="number"
                            wire:model="priority"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                            min="0"
                        >
                        @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Higher priority offers apply first</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        wire:model="description"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2"
                        rows="3"
                        placeholder="Optional description"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                        <input
                            type="date"
                            wire:model="start_date"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                        >
                        @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                        <input
                            type="date"
                            wire:model="end_date"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                        >
                        @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="form-checkbox">
                        <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <!-- Offer Type -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Offer Type</h2>

                <div class="flex space-x-4 mb-4">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="offer_type"
                            value="buy_x_get_y"
                            class="form-radio"
                        >
                        <span class="ml-2">Buy X Get Y Free</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="offer_type"
                            value="quantity_discount"
                            class="form-radio"
                        >
                        <span class="ml-2">Quantity Discount</span>
                    </label>
                </div>

                @if($offer_type === 'buy_x_get_y')
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buy Quantity *</label>
                            <input
                                type="number"
                                wire:model="buy_quantity"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                min="1"
                            >
                            @error('buy_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Get Free Quantity *</label>
                            <input
                                type="number"
                                wire:model="get_quantity"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                min="1"
                            >
                            @error('get_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-4 bg-gray-50 p-4 rounded">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Quantity *</label>
                            <input
                                type="number"
                                wire:model="min_quantity"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                min="1"
                                step="0.01"
                            >
                            @error('min_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                            <select wire:model="discount_type" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                            <input
                                type="number"
                                wire:model="discount_value"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                min="0.01"
                                step="0.01"
                            >
                            @error('discount_value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif
            </div>

            <!-- Applicable Products/Categories -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Applicable To</h2>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Products -->
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Products</h3>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="productSearch"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-2"
                            placeholder="Search products..."
                        >
                        <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto">
                            @foreach($products as $product)
                                <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleProduct({{ $product->id }})"
                                        @checked(in_array($product->id, $selectedProducts))
                                        class="form-checkbox"
                                    >
                                    <span class="ml-2 text-sm">{{ $product->name }} ({{ $product->sku }})</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ count($selectedProducts) }} product(s) selected</p>
                    </div>

                    <!-- Categories -->
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Categories</h3>
                        <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleCategory({{ $category->id }})"
                                        @checked(in_array($category->id, $selectedCategories))
                                        class="form-checkbox"
                                    >
                                    <span class="ml-2 text-sm">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ count($selectedCategories) }} category(ies) selected</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('offers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                    {{ $isEdit ? 'Update Offer' : 'Create Offer' }}
                </button>
            </div>
        </form>
    </div>
</div>
