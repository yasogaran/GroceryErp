<div>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-blue-100">
                Here's an overview of your grocery store operations
            </p>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Today's Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow">
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

            <!-- Monthly Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Monthly Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthSales, 2) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
                        @if($lowStockProducts > 0)
                            <p class="text-xs text-red-600 mt-1">{{ $lowStockProducts }} low stock</p>
                        @endif
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Suppliers -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Suppliers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSuppliers) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Users -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-pink-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">System Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                    </div>
                    <div class="p-3 bg-pink-100 rounded-full">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending GRNs -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500 hover:shadow-md transition-shadow">
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

            <!-- Low Stock Alert -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Alerts</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($lowStockProducts) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Charts and Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products (Last 30 Days)</h3>
                @if($topSellingProducts->count() > 0)
                    <div class="space-y-3">
                        @foreach($topSellingProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-sm text-gray-600">Sold: {{ number_format($product->total_sold) }} units</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600">Rs. {{ number_format($product->revenue, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No sales data available</p>
                @endif
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Low Stock Alerts
                </h3>
                @if($stockAlerts->count() > 0)
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($stockAlerts as $product)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-600">SKU: {{ $product->sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-red-600">{{ number_format($product->current_stock_quantity) }}</p>
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

        <!-- Recent Sales -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Sales</h3>
            @if($recentSales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentSales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $sale->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->customer?->name ?? 'Walk-in' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->cashier?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rs. {{ number_format($sale->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($sale->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sale->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No recent sales</p>
            @endif
        </div>

        <!-- Sales by Payment Mode (Today) -->
        @if($salesByPaymentMode->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Sales by Payment Mode</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($salesByPaymentMode as $payment)
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-sm font-medium text-gray-600 uppercase">{{ $payment->payment_mode }}</p>
                            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($payment->total, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
