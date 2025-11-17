{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    {{-- Today's Sales Card --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ format_currency($data['todaySales']) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $data['todayTransactions'] }} transactions
                </p>
            </div>
            <div class="text-4xl">💰</div>
        </div>
        @if($data['yesterdaySales'] > 0)
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs font-medium {{ $data['salesChange'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $data['salesChange'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($data['salesChange']), 1) }}% from yesterday
                </span>
            </div>
        @endif
    </div>

    {{-- Total Customers Card --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">Total Customers</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ number_format($data['totalCustomers']) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $data['newCustomersThisMonth'] }} new this month
                </p>
            </div>
            <div class="text-4xl">👥</div>
        </div>
        @if($data['customerGrowth'] != 0)
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs font-medium {{ $data['customerGrowth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $data['customerGrowth'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($data['customerGrowth']), 1) }}% growth
                </span>
            </div>
        @endif
    </div>

    {{-- Stock Value Card --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">Stock Value</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ format_currency($data['stockValue']) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Current inventory value
                </p>
            </div>
            <div class="text-4xl">📦</div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs">
            <span class="text-orange-600 font-medium">{{ $data['lowStockCount'] }} low stock</span>
            <span class="text-red-600 font-medium">{{ $data['outOfStockCount'] }} out of stock</span>
        </div>
    </div>

    {{-- Pending Actions Card --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">Pending Actions</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $data['totalPendingActions'] }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Items requiring attention
                </p>
            </div>
            <div class="text-4xl">⚠️</div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs">
            <span class="text-gray-600">{{ $data['pendingGRNs'] }} GRNs</span>
            <span class="text-gray-600">{{ $data['pendingReturns'] }} Returns</span>
        </div>
    </div>
</div>

{{-- Sales Chart --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Sales Trend</h3>
        <div class="flex gap-2">
            <button wire:click="updateChartPeriod('7days')" class="px-3 py-1 text-xs rounded {{ $chartPeriod === '7days' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">7 Days</button>
            <button wire:click="updateChartPeriod('30days')" class="px-3 py-1 text-xs rounded {{ $chartPeriod === '30days' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">30 Days</button>
            <button wire:click="updateChartPeriod('90days')" class="px-3 py-1 text-xs rounded {{ $chartPeriod === '90days' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">90 Days</button>
        </div>
    </div>
    <div style="height: 300px;">
        <canvas id="salesChart"></canvas>
    </div>
</div>

{{-- Two Column Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Top Selling Products --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Selling Products (Today)</h3>
        @if(count($data['topProducts']) > 0)
            <div class="space-y-3">
                @foreach($data['topProducts'] as $index => $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full text-sm font-bold">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($product['units_sold']) }} units sold</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">{{ format_currency($product['revenue']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p>No sales recorded today</p>
            </div>
        @endif
    </div>

    {{-- Recent Sales --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h3>
        @if($data['recentSales']->count() > 0)
            <div class="space-y-3">
                @foreach($data['recentSales'] as $sale)
                    <div class="flex items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }} •
                                {{ $sale->sale_date->format('h:i A') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">{{ format_currency($sale->total_amount) }}</p>
                            <p class="text-xs text-gray-500">{{ $sale->cashier ? $sale->cashier->name : '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p>No recent sales</p>
            </div>
        @endif
    </div>
</div>

{{-- Financial Summary --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Financial Overview</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Cash in Hand</p>
            <p class="text-xl font-bold text-gray-900">{{ format_currency($data['cashInHand']) }}</p>
        </div>
        <div class="p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Bank Balance</p>
            <p class="text-xl font-bold text-gray-900">{{ format_currency($data['bankBalance']) }}</p>
        </div>
        <div class="p-4 bg-purple-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Accounts Receivable</p>
            <p class="text-xl font-bold text-gray-900">{{ format_currency($data['accountsReceivable']) }}</p>
        </div>
        <div class="p-4 bg-orange-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Accounts Payable</p>
            <p class="text-xl font-bold text-gray-900">{{ format_currency($data['accountsPayable']) }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($data['salesChartData']['labels']),
                    datasets: [{
                        label: 'Sales ({{ currency_symbol() }})',
                        data: @json($data['salesChartData']['data']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Sales: {{ currency_symbol() }} ' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ currency_symbol() }} ' + value.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&,');
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
