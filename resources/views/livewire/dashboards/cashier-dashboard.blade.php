<div>
    <x-slot name="header">
        Cashier Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-purple-100">
                Your sales performance and shift summary
            </p>
        </div>

        <!-- Shift Status Alert -->
        @if($currentShift)
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Shift Active</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Started: {{ $currentShift->shift_start->format('M d, Y H:i') }}</p>
                            <p>Opening Balance: Rs. {{ number_format($currentShift->opening_balance, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">No Active Shift</h3>
                        <p class="mt-1 text-sm text-yellow-700">Please start a shift before making sales</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Shift Metrics (if shift is active) -->
        @if($currentShift)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Current Shift Sales</p>
                            <p class="text-3xl font-bold text-gray-900">Rs. {{ number_format($shiftSales, 2) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Shift Transactions</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($shiftTransactions) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Mode Breakdown -->
            @if($shiftPaymentBreakdown->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shift Payment Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($shiftPaymentBreakdown as $payment)
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm font-medium text-gray-600 uppercase">{{ $payment->payment_mode }}</p>
                                <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($payment->total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <!-- Today's Overall Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Total Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todaySales, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($todayTransactions) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </div>

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

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-pink-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <div class="p-3 bg-pink-100 rounded-full">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: Top Products and Recent Sales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Selling Today -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products Today</h3>
                @if($topSellingToday->count() > 0)
                    <div class="space-y-3">
                        @foreach($topSellingToday as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600">{{ number_format($product->total_sold) }} units</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No sales today yet</p>
                @endif
            </div>

            <!-- My Recent Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">My Recent Sales Today</h3>
                @if($recentSales->count() > 0)
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($recentSales as $sale)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $sale->invoice_number }}</p>
                                    <p class="text-sm text-gray-600">{{ $sale->customer?->name ?? 'Walk-in' }}</p>
                                    <p class="text-xs text-gray-500">{{ $sale->items->count() }} items | {{ $sale->created_at->format('H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rs. {{ number_format($sale->total_amount, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No sales yet today</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('pos') }}" class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border-2 border-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="font-semibold text-blue-700">New Sale</span>
                </a>

                <a href="{{ route('returns.manage') }}" class="flex items-center justify-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border-2 border-orange-200 transition-colors">
                    <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                    <span class="font-semibold text-orange-700">Process Return</span>
                </a>

                @if(!$currentShift)
                    <a href="{{ route('shifts.manage') }}" class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border-2 border-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold text-green-700">Start Shift</span>
                    </a>
                @else
                    <a href="{{ route('shifts.manage') }}" class="flex items-center justify-center p-4 bg-red-50 hover:bg-red-100 rounded-lg border-2 border-red-200 transition-colors">
                        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="font-semibold text-red-700">Close Shift</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
