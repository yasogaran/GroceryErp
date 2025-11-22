<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Stock Inventory Report</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Comprehensive stock inventory with export options</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Products</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_products']) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Stock Value</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($stats['total_value'], 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Out of Stock</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['out_of_stock']) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Low Stock</div>
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($stats['low_stock']) }}</div>
        </div>
    </div>

    <!-- Filters and Export -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="SKU, Name, Barcode..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select wire:model.live="categoryFilter"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Status</label>
                    <select wire:model.live="stockStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="all">All Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Show</label>
                    <select wire:model.live="perPage"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $products->count() }} of {{ $products->total() }} products
            </div>
            <div class="flex gap-2">
                <button wire:click="exportToExcel"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPdf"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th wire:click="sortBy('sku')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            SKU
                            @if($sortBy === 'sku')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Barcode
                        </th>
                        <th wire:click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            Product Name
                            @if($sortBy === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Category
                        </th>
                        <th wire:click="sortBy('quantity')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            Stock
                            @if($sortBy === 'quantity')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Min/Max
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Cost Price
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Selling Price
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Stock Value
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $product->barcode }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $product->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $product->category?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ number_format($product->quantity, 2) }} {{ $product->unit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-400">
                                {{ $product->minimum_quantity }} / {{ $product->maximum_quantity ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                Rs. {{ number_format($product->cost_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                Rs. {{ number_format($product->selling_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                                Rs. {{ number_format($product->quantity * $product->cost_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($product->quantity == 0)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Out of Stock
                                    </span>
                                @elseif($product->quantity <= $product->minimum_quantity)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $products->links() }}
        </div>
    </div>
</div>
