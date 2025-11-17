<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Daily Sales Report</h1>

        <input
            type="date"
            wire:model.change="reportDate"
            class="border border-gray-300 rounded-lg px-4 py-2"
        >
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Total Sales</p>
            <p class="text-3xl font-bold text-blue-600">{{ format_currency($summary['total_sales']) }}</p>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Transactions</p>
            <p class="text-3xl font-bold text-green-600">{{ $summary['total_transactions'] }}</p>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Cash Sales</p>
            <p class="text-3xl font-bold text-purple-600">{{ format_currency($summary['cash_sales']) }}</p>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Avg Transaction</p>
            <p class="text-3xl font-bold text-orange-600">{{ format_currency($summary['avg_transaction']) }}</p>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Top Selling Products</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">#</th>
                        <th class="text-left py-3 px-4">Product</th>
                        <th class="text-right py-3 px-4">Quantity Sold</th>
                        <th class="text-right py-3 px-4">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">{{ $item->product->name }}</td>
                            <td class="py-3 px-4 text-right">{{ number_format($item->total_qty, 0) }}</td>
                            <td class="py-3 px-4 text-right font-medium">{{ format_currency($item->total_value) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- All Sales -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">All Sales ({{ $sales->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Invoice</th>
                        <th class="text-left py-3 px-4">Time</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-left py-3 px-4">Cashier</th>
                        <th class="text-right py-3 px-4">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">{{ $sale->invoice_number }}</td>
                            <td class="py-3 px-4">{{ $sale->sale_date->format('H:i:s') }}</td>
                            <td class="py-3 px-4">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4">{{ $sale->cashier->name }}</td>
                            <td class="py-3 px-4 text-right font-medium">{{ format_currency($sale->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">No sales for this date</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
