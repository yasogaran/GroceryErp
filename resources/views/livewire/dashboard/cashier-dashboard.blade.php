{{-- Shift Status Card --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Shift Status</h3>
    @if($data['hasOpenShift'])
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600">Shift Status</p>
                    <p class="text-xl font-bold text-green-600">OPEN</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Opening Cash</p>
                    <p class="text-xl font-bold text-gray-900">{{ format_currency($data['currentShift']->opening_cash) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Time Elapsed</p>
                    <p class="text-xl font-bold text-gray-900">{{ $data['currentShift']->shift_start->diffForHumans(null, true) }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('pos') }}" class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                    Start POS
                </a>
                <a href="{{ route('shifts.close') }}" class="flex-1 bg-gray-600 text-white text-center py-2 rounded-lg hover:bg-gray-700 transition">
                    Close Shift
                </a>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="text-center mb-4">
                <p class="text-sm text-gray-600 mb-2">No Active Shift</p>
                <p class="text-xl font-bold text-yellow-600">SHIFT CLOSED</p>
                <p class="text-sm text-gray-500 mt-2">You need to open a shift before you can make sales</p>
            </div>
            <a href="{{ route('shifts.open') }}" class="block bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition">
                Open New Shift
            </a>
        </div>
    @endif
</div>

{{-- Today's Performance --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
        <p class="text-sm font-medium text-gray-600">My Sales Today</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ format_currency($data['todaySales']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total sales amount</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
        <p class="text-sm font-medium text-gray-600">Transactions</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($data['todayTransactions']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Number of sales</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
        <p class="text-sm font-medium text-gray-600">Average Transaction</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ format_currency($data['avgTransactionValue']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Per transaction</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('pos') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-12 h-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">POS</span>
        </a>

        <a href="{{ route('sales.index') }}" class="flex flex-col items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
            <svg class="w-12 h-12 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">My Sales</span>
        </a>

        <a href="{{ route('returns.create') }}" class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
            <svg class="w-12 h-12 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Process Return</span>
        </a>

        <a href="{{ route('customers.index') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
            <svg class="w-12 h-12 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Customers</span>
        </a>
    </div>
</div>

{{-- Recent Sales --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">My Recent Sales</h3>
    @if($data['recentSales']->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['recentSales'] as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date->format('h:i A') }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">{{ format_currency($sale->total_amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <p>No sales recorded yet today</p>
        </div>
    @endif
</div>
