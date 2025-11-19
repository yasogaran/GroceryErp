<div class="bg-white rounded-lg shadow-sm p-4 h-full">
    <!-- Header Section with Search, Toggles -->
    <div class="mb-4 space-y-3">
        <!-- Top Row: Search Bar with Toggles -->
        <div class="flex gap-3 items-center">
            <!-- Search Bar -->
            <div class="flex-1">
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

            <!-- Batch Selection Toggle Switch -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 shrink-0">
                <span class="text-sm font-medium text-gray-700">Stock:</span>
                <button
                    wire:click="toggleBatchSelection"
                    class="relative inline-flex h-8 w-16 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $showBatchSelection ? 'bg-green-500' : 'bg-blue-500' }}"
                    role="switch"
                    aria-checked="{{ $showBatchSelection ? 'true' : 'false' }}"
                    title="Toggle between Batch Mode and Auto Mode"
                >
                    <span class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform {{ $showBatchSelection ? 'translate-x-9' : 'translate-x-1' }}"></span>
                </button>
                <span class="text-xs font-bold {{ $showBatchSelection ? 'text-green-600' : 'text-blue-600' }}">
                    {{ $showBatchSelection ? 'Batch' : 'Auto' }}
                </span>
            </div>

            <!-- View Toggle (Card/List) -->
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1 shrink-0">
                <button
                    wire:click="$set('viewMode', 'grid')"
                    class="p-2 rounded {{ $viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}"
                    title="Grid View"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </button>
                <button
                    wire:click="$set('viewMode', 'list')"
                    class="p-2 rounded {{ $viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}"
                    title="List View"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Category Filters - Horizontal Scroll with Buttons -->
        <div class="relative flex items-center gap-2">
            <!-- Left Scroll Button -->
            <button
                type="button"
                onclick="document.getElementById('category-scroll').scrollBy({left: -200, behavior: 'smooth'})"
                class="shrink-0 bg-white border border-gray-300 hover:bg-gray-50 rounded-full p-2 shadow-sm z-10"
                title="Scroll Left"
            >
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <!-- Categories Container - Hidden Scrollbar -->
            <div id="category-scroll" class="overflow-x-auto flex-1" style="scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #category-scroll::-webkit-scrollbar {
                        display: none;
                    }
                </style>
                <div class="flex gap-2 pb-2 min-w-max">
                    <button
                        wire:click="clearCategory"
                        class="px-4 py-2 rounded-full text-sm whitespace-nowrap {{ is_null($selectedCategory) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                    >
                        All Categories
                    </button>
                    @foreach($categories as $category)
                        <button
                            wire:click="selectCategory({{ $category->id }})"
                            class="px-4 py-2 rounded-full text-sm whitespace-nowrap {{ $selectedCategory == $category->id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                        >
                            {{ $category->name }} ({{ $category->products_count }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right Scroll Button -->
            <button
                type="button"
                onclick="document.getElementById('category-scroll').scrollBy({left: 200, behavior: 'smooth'})"
                class="shrink-0 bg-white border border-gray-300 hover:bg-gray-50 rounded-full p-2 shadow-sm z-10"
                title="Scroll Right"
            >
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Products Display Area -->
    <div class="overflow-y-auto" style="max-height: calc(100vh - 330px);">
        @if($viewMode === 'grid')
            <!-- Grid View -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @forelse($products as $product)
            @if($showBatchSelection && isset($productBatches[$product->id]) && count($productBatches[$product->id]) > 0)
                <!-- Show each batch as a separate card -->
                @foreach($productBatches[$product->id] as $batch)
                    <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow {{ isset($batch['expiry_date']) && \Carbon\Carbon::parse($batch['expiry_date'])->lte(\Carbon\Carbon::now()->addDays(30)) ? 'border-yellow-400 bg-yellow-50' : '' }}">
                        <!-- Product Image -->
                        @if($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded mb-2">
                        @else
                            <div class="w-full h-20 bg-gray-200 rounded mb-2 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Product Info -->
                        <h4 class="font-medium text-sm mb-1 line-clamp-2">{{ $product->name }}</h4>
                        <p class="text-xs text-gray-500 mb-1">{{ $product->category->name ?? 'N/A' }}</p>

                        <!-- Batch Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded p-2 mb-2 text-xs">
                            @if($batch['batch_number'])
                                <p class="font-semibold text-blue-900">Batch: {{ $batch['batch_number'] }}</p>
                            @endif
                            @if(isset($batch['expiry_date']))
                                <p class="text-blue-700">
                                    Exp: {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M d, Y') }}
                                    @if(\Carbon\Carbon::parse($batch['expiry_date'])->lte(\Carbon\Carbon::now()->addDays(30)))
                                        <span class="text-yellow-600 font-bold">⚠</span>
                                    @endif
                                </p>
                            @endif
                            <p class="text-blue-700">Stock: {{ number_format($batch['remaining_quantity'], 0) }} {{ $product->base_unit }}</p>
                        </div>

                        <!-- Price -->
                        <p class="text-lg font-bold text-green-600 mb-2">Rs. {{ number_format($batch['max_selling_price'], 2) }}</p>

                        <!-- Add Buttons -->
                        <div class="flex gap-2">
                            <button
                                wire:click="addToCart({{ $product->id }}, false, {{ $batch['stock_movement_id'] }})"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs py-2 rounded font-medium"
                            >
                                + Piece
                            </button>

                            @if($product->has_packaging && $product->packaging)
                                <button
                                    wire:click="addToCart({{ $product->id }}, true, {{ $batch['stock_movement_id'] }})"
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white text-xs py-2 rounded font-medium"
                                    title="Box of {{ $product->packaging->pieces_per_package }} pcs"
                                >
                                    + Box
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Show product card (Auto FIFO mode) -->
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
            @endif
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
        @else
            <!-- List View -->
            <div class="space-y-2">
        @forelse($products as $product)
            @if($showBatchSelection && isset($productBatches[$product->id]) && count($productBatches[$product->id]) > 0)
                <!-- Show each batch as a separate row -->
                @foreach($productBatches[$product->id] as $batch)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow {{ isset($batch['expiry_date']) && \Carbon\Carbon::parse($batch['expiry_date'])->lte(\Carbon\Carbon::now()->addDays(30)) ? 'border-yellow-400 bg-yellow-50' : 'bg-white' }}">
                        <div class="flex gap-4 items-center">
                            <!-- Product Image -->
                            <div class="shrink-0">
                                @if($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-20 h-20 object-cover rounded">
                                @else
                                    <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Product Info -->
                                <div>
                                    <h4 class="font-semibold text-base mb-1">{{ $product->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $product->category->name ?? 'N/A' }}</p>
                                </div>

                                <!-- Batch Info -->
                                <div>
                                    @if($batch['batch_number'])
                                        <p class="text-sm"><span class="font-medium text-gray-700">Batch:</span> <span class="text-blue-600 font-semibold">{{ $batch['batch_number'] }}</span></p>
                                    @endif
                                    @if(isset($batch['expiry_date']))
                                        <p class="text-sm">
                                            <span class="font-medium text-gray-700">Expiry:</span>
                                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M d, Y') }}</span>
                                            @if(\Carbon\Carbon::parse($batch['expiry_date'])->lte(\Carbon\Carbon::now()->addDays(30)))
                                                <span class="text-yellow-600 font-bold ml-1">⚠</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                <!-- Stock & Price -->
                                <div>
                                    <p class="text-sm"><span class="font-medium text-gray-700">Stock:</span> <span class="text-gray-900 font-semibold">{{ number_format($batch['remaining_quantity'], 0) }}</span> {{ $product->base_unit }}</p>
                                    <p class="text-lg font-bold text-green-600 mt-1">Rs. {{ number_format($batch['max_selling_price'], 2) }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2 items-center justify-end">
                                    <button
                                        wire:click="addToCart({{ $product->id }}, false, {{ $batch['stock_movement_id'] }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded font-medium text-sm"
                                    >
                                        + Piece
                                    </button>

                                    @if($product->has_packaging && $product->packaging)
                                        <button
                                            wire:click="addToCart({{ $product->id }}, true, {{ $batch['stock_movement_id'] }})"
                                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-medium text-sm"
                                            title="Box of {{ $product->packaging->pieces_per_package }} pcs"
                                        >
                                            + Box
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Show product row (Auto FIFO mode) -->
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
                    <div class="flex gap-4 items-center">
                        <!-- Product Image -->
                        <div class="shrink-0">
                            @if($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-20 h-20 object-cover rounded">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Product Info -->
                            <div>
                                <h4 class="font-semibold text-base mb-1">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $product->category->name ?? 'N/A' }}</p>
                            </div>

                            <!-- SKU/Barcode -->
                            <div>
                                <p class="text-sm"><span class="font-medium text-gray-700">SKU:</span> <span class="text-gray-900">{{ $product->sku }}</span></p>
                                @if($product->barcode)
                                    <p class="text-sm"><span class="font-medium text-gray-700">Barcode:</span> <span class="text-gray-900">{{ $product->barcode }}</span></p>
                                @endif
                            </div>

                            <!-- Stock & Price -->
                            <div>
                                <p class="text-sm"><span class="font-medium text-gray-700">Stock:</span> <span class="text-gray-900 font-semibold">{{ number_format($product->current_stock_quantity, 0) }}</span> pcs</p>
                                <p class="text-lg font-bold text-blue-600 mt-1">Rs. {{ number_format($product->max_selling_price, 2) }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 items-center justify-end">
                                <button
                                    wire:click="addToCart({{ $product->id }}, false)"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded font-medium text-sm"
                                >
                                    + Piece
                                </button>

                                @if($product->has_packaging && $product->packaging)
                                    <button
                                        wire:click="addToCart({{ $product->id }}, true)"
                                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-medium text-sm"
                                        title="Box of {{ $product->packaging->pieces_per_package }} pcs"
                                    >
                                        + Box
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-12 text-gray-500">
                @if($searchTerm)
                    No products found for "{{ $searchTerm }}"
                @else
                    No products available
                @endif
            </div>
        @endforelse
            </div>
        @endif
    </div>

    <!-- Inline Styles -->
    <style>
        /* Custom scrollbar styling */
        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>

    <!-- Inline Scripts -->
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
</div>
