<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Loyalty Points History</h1>
        <p class="text-gray-600 mt-2">Customer: {{ $customer->name }} ({{ $customer->customer_code }})</p>
    </div>

    <!-- Points Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Current Balance</p>
            <p class="text-3xl font-bold text-blue-600">{{ number_format($summary['balance'], 0) }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Earned</p>
            <p class="text-3xl font-bold text-green-600">{{ number_format($summary['total_earned'], 0) }}</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Redeemed</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($summary['total_redeemed'], 0) }}</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Purchases</p>
            <p class="text-2xl font-bold text-purple-600">{{ format_currency($customer->total_purchases) }}</p>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 text-sm">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $transaction->transaction_type === 'earned' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ ucfirst($transaction->transaction_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium {{ $transaction->points > 0 ? 'text-green-600' : 'text-orange-600' }}">
                            {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points, 0) }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($transaction->reference_type === 'sale' && $transaction->sale)
                                <a href="{{ route('sales.show', $transaction->reference_id) }}" class="text-blue-600 hover:underline">
                                    {{ $transaction->sale->invoice_number }}
                                </a>
                            @else
                                {{ ucfirst($transaction->reference_type ?? 'N/A') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ $transaction->notes ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ $transaction->creator->name ?? 'System' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $transactions->links() }}
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('customers.index') }}" class="text-blue-600 hover:underline">
            &larr; Back to Customers
        </a>
    </div>
</div>
