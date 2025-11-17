{{-- Inventory Status Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
        <p class="text-sm font-medium text-gray-600">Total Products</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($data['totalProducts']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Active products</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-orange-500">
        <p class="text-sm font-medium text-gray-600">Low Stock</p>
        <p class="text-3xl font-bold text-orange-600 mt-2">{{ number_format($data['lowStockCount']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Below reorder level</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
        <p class="text-sm font-medium text-gray-600">Out of Stock</p>
        <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($data['outOfStockCount']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Require immediate attention</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
        <p class="text-sm font-medium text-gray-600">Expiring Soon</p>
        <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($data['expiringSoon']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Next 30 days</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('grn.create') }}" class="flex flex-col items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
            <svg class="w-12 h-12 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Receive Goods</span>
        </a>

        <a href="{{ route('stock.adjustment') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-12 h-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 7l1 12a2 2 0 002 2h10a2 2 0 002-2l1-12M4 7h16M10 11v6m4-6v6m-6-8l2-2m8 2l-2-2" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Stock Adjustment</span>
        </a>

        <a href="{{ route('inventory.report') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
            <svg class="w-12 h-12 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Inventory Report</span>
        </a>

        <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
            <svg class="w-12 h-12 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">View Products</span>
        </a>
    </div>
</div>

{{-- Two Column Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Low Stock Products --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Low Stock Products</h3>
        @if($data['lowStockProducts']->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Reorder</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['lowStockProducts'] as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-2 text-sm text-right">
                                    <span class="font-bold {{ $product->current_stock_quantity <= 0 ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ number_format($product->current_stock_quantity, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 text-right">{{ number_format($product->reorder_level, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p>All products are adequately stocked</p>
            </div>
        @endif
    </div>

    {{-- Recent Stock Movements --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Stock Movements</h3>
        @if($data['recentStockMovements']->count() > 0)
            <div class="space-y-3">
                @foreach($data['recentStockMovements'] as $movement)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $movement->product->name ?? 'Unknown Product' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst($movement->movement_type) }} • {{ $movement->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $movement->movement_type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement->movement_type === 'in' ? '+' : '-' }}{{ number_format(abs($movement->quantity), 2) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p>No recent stock movements</p>
            </div>
        @endif
    </div>
</div>
