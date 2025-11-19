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
            <p class="text-3xl font-bold text-blue-600">Rs. {{ number_format($summary['total_sales'], 2) }}</p>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Transactions</p>
            <p class="text-3xl font-bold text-green-600">{{ $summary['total_transactions'] }}</p>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Cash Sales</p>
            <p class="text-3xl font-bold text-purple-600">Rs. {{ number_format($summary['cash_sales'], 2) }}</p>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-2">Avg Transaction</p>
            <p class="text-3xl font-bold text-orange-600">Rs. {{ number_format($summary['avg_transaction'], 2) }}</p>
        </div>
    </div>

    <!-- Cash Account Activity -->
    @if($cashActivity['account_exists'])
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Cash Account Activity</h2>
                <p class="text-sm text-gray-500">{{ $cashActivity['account_name'] }} ({{ $cashActivity['account_code'] }})</p>
            </div>
        </div>

        <!-- Cash Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Opening Balance</p>
                <p class="text-xl font-bold {{ $cashActivity['opening_balance'] >= 0 ? 'text-gray-700' : 'text-red-600' }}">
                    Rs. {{ number_format($cashActivity['opening_balance'], 2) }}
                </p>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Cash Inflows</p>
                <p class="text-xl font-bold text-green-600">
                    Rs. {{ number_format($cashActivity['total_inflows'], 2) }}
                </p>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Cash Outflows</p>
                <p class="text-xl font-bold text-red-600">
                    Rs. {{ number_format($cashActivity['total_outflows'], 2) }}
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Closing Balance</p>
                <p class="text-xl font-bold {{ $cashActivity['closing_balance'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rs. {{ number_format($cashActivity['closing_balance'], 2) }}
                </p>
            </div>
        </div>

        <!-- Cash Transactions Detail -->
        @if(count($cashActivity['transactions']) > 0)
        <div class="overflow-x-auto">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Transaction Details</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left py-2 px-3">Time</th>
                        <th class="text-left py-2 px-3">Entry #</th>
                        <th class="text-left py-2 px-3">Description</th>
                        <th class="text-left py-2 px-3">Type</th>
                        <th class="text-right py-2 px-3">Cash In</th>
                        <th class="text-right py-2 px-3">Cash Out</th>
                        <th class="text-right py-2 px-3">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashActivity['transactions'] as $transaction)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $transaction['time'] }}</td>
                            <td class="py-2 px-3 font-mono text-xs">{{ $transaction['entry_number'] }}</td>
                            <td class="py-2 px-3">{{ $transaction['description'] }}</td>
                            <td class="py-2 px-3">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                                    {{ $transaction['reference_type'] }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-right {{ $transaction['debit'] > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                {{ $transaction['debit'] > 0 ? 'Rs. ' . number_format($transaction['debit'], 2) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right {{ $transaction['credit'] > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                {{ $transaction['credit'] > 0 ? 'Rs. ' . number_format($transaction['credit'], 2) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right font-medium {{ $transaction['balance'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                Rs. {{ number_format($transaction['balance'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 font-bold bg-gray-50">
                        <td colspan="4" class="py-3 px-3 text-right">TOTALS:</td>
                        <td class="py-3 px-3 text-right text-green-600">Rs. {{ number_format($cashActivity['total_inflows'], 2) }}</td>
                        <td class="py-3 px-3 text-right text-red-600">Rs. {{ number_format($cashActivity['total_outflows'], 2) }}</td>
                        <td class="py-3 px-3 text-right {{ $cashActivity['closing_balance'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            Rs. {{ number_format($cashActivity['closing_balance'], 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>No cash transactions recorded for this date</p>
        </div>
        @endif
    </div>
    @endif

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
                            <td class="py-3 px-4 text-right font-medium">Rs. {{ number_format($item->total_value, 2) }}</td>
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
                            <td class="py-3 px-4 text-right font-medium">Rs. {{ number_format($sale->total_amount, 2) }}</td>
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
