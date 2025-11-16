<div class="bg-white rounded-lg shadow-sm p-4 h-full">
    <!-- Search Bar -->
    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="searchTerm"
            placeholder="Search by name, SKU, or scan barcode..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
            autofocus
            id="product-search-input"
        >
        <p class="text-xs text-gray-500 mt-1">Press F1 to focus search | Scan barcode to auto-add</p>
    </div>

    <!-- Category Filters -->
    <div class="mb-4 flex flex-wrap gap-2">
        <button
            wire:click="clearCategory"
            class="px-3 py-1 rounded-full text-sm {{ is_null($selectedCategory) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}"
        >
            All Categories
        </button>
        @foreach($categories as $category)
            <button
                wire:click="selectCategory({{ $category->id }})"
                class="px-3 py-1 rounded-full text-sm {{ $selectedCategory == $category->id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}"
            >
                {{ $category->name }} ({{ $category->products_count }})
            </button>
        @endforeach
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 overflow-y-auto" style="max-height: calc(100vh - 300px);">
        @forelse($products as $product)
            <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                <!-- Product Image -->
                @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded mb-2">
                @else
                    <div class="w-full h-24 bg-gray-200 rounded mb-2 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif

                <!-- Product Info -->
                <h4 class="font-medium text-sm mb-1 line-clamp-2">{{ $product->name }}</h4>
                <p class="text-xs text-gray-500 mb-2">{{ $product->category->name ?? 'N/A' }}</p>
                <p class="text-lg font-bold text-blue-600 mb-2">Rs. {{ number_format($product->max_selling_price, 2) }}</p>

                <!-- Stock Info -->
                <p class="text-xs text-gray-600 mb-3">
                    Stock: {{ number_format($product->current_stock_quantity, 0) }} pcs
                </p>

                <!-- Add Buttons -->
                <div class="flex gap-2">
                    <button
                        wire:click="addToCart({{ $product->id }}, false)"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs py-2 rounded font-medium"
                    >
                        + Piece
                    </button>

                    @if($product->has_packaging && $product->packaging)
                        <button
                            wire:click="addToCart({{ $product->id }}, true)"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white text-xs py-2 rounded font-medium"
                            title="Box of {{ $product->packaging->pieces_per_package }} pcs"
                        >
                            + Box
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                @if($searchTerm)
                    No products found for "{{ $searchTerm }}"
                @else
                    No products available
                @endif
            </div>
        @endforelse
    </div>
</div>

<script>
    // Auto-focus search on F1
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            document.getElementById('product-search-input')?.focus();
        }
    });

    // Listen for focus event from Livewire
    window.addEventListener('focusSearch', () => {
        setTimeout(() => {
            document.getElementById('product-search-input')?.focus();
        }, 100);
    });
</script>
