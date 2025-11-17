<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Stock Report</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Total Products</p>
            <p class="text-3xl font-bold text-blue-600">{{ $summary['total_products'] }}</p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Out of Stock</p>
            <p class="text-3xl font-bold text-red-600">{{ $summary['out_of_stock'] }}</p>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Low Stock</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $summary['low_stock'] }}</p>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Total Stock Value</p>
            <p class="text-3xl font-bold text-green-600">{{ format_currency($summary['total_value']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Search products..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-2"
            >

            <select
                wire:model.change="categoryFilter"
                class="border border-gray-300 rounded-lg px-4 py-2"
            >
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <label class="flex items-center space-x-2 border border-gray-300 rounded-lg px-4 py-2 cursor-pointer">
                <input
                    type="checkbox"
                    wire:model.change="lowStockOnly"
                    class="form-checkbox"
                >
                <span>Low Stock Only</span>
            </label>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4">Product</th>
                    <th class="text-left py-3 px-4">Category</th>
                    <th class="text-right py-3 px-4">Current Stock</th>
                    <th class="text-right py-3 px-4">Price</th>
                    <th class="text-right py-3 px-4">Stock Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <p class="font-medium">{{ $product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $product->sku }}</p>
                        </td>
                        <td class="py-3 px-4">{{ $product->category?->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-right">
                            <span class="@if($product->current_stock_quantity == 0) text-red-600 font-bold @elseif($product->current_stock_quantity <= settings('low_stock_threshold', 10)) text-yellow-600 font-medium @endif">
                                {{ number_format($product->current_stock_quantity, 0) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">{{ format_currency($product->max_selling_price) }}</td>
                        <td class="py-3 px-4 text-right font-medium">
                            {{ format_currency($product->current_stock_quantity * $product->max_selling_price) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
