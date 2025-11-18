<div>
    <x-slot name="header">
        Manager Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-indigo-100">
                Monitor store operations and inventory management
            </p>
        </div>

        <!-- Sales Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Today's Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todaySales, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Weekly Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Week's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($weekSales, 2) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Month's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthSales, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory & Operations Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-cyan-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
                    </div>
                    <div class="p-3 bg-cyan-100 rounded-full">
                        <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($lowStockProducts) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($outOfStockProducts) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending GRNs -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending GRNs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingGRNs) }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: Charts and Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products (Last 7 Days)</h3>
                @if($topSellingProducts->count() > 0)
                    <div class="space-y-3">
                        @foreach($topSellingProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600">{{ number_format($product->total_sold) }} units</p>
                                    <p class="text-xs text-gray-600">Rs. {{ number_format($product->revenue, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No sales data available</p>
                @endif
            </div>

            <!-- Critical Stock Alerts -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Critical Stock Alerts
                </h3>
                @if($stockAlerts->count() > 0)
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($stockAlerts as $product)
                            <div class="flex items-center justify-between p-3 rounded-lg border
                                {{ $product->current_stock_quantity <= 0 ? 'bg-red-100 border-red-300' : 'bg-yellow-50 border-yellow-200' }}">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-600">SKU: {{ $product->sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold {{ $product->current_stock_quantity <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                        {{ number_format($product->current_stock_quantity) }}
                                    </p>
                                    <p class="text-xs text-gray-500">Min: {{ number_format($product->reorder_level) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">All products are well stocked!</p>
                @endif
            </div>
        </div>

        <!-- Recent GRNs -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent GRNs</h3>
            @if($recentGRNs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentGRNs as $grn)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $grn->grn_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $grn->supplier->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rs. {{ number_format($grn->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $grn->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($grn->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $grn->creator->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $grn->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No recent GRNs</p>
            @endif
        </div>

        <!-- Supplier Outstanding -->
        @if($supplierOutstanding > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Supplier Outstanding Balance</h4>
                        <p class="mt-1 text-2xl font-bold text-yellow-900">Rs. {{ number_format($supplierOutstanding, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
