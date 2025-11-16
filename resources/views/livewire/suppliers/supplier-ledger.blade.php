<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Supplier Ledger</h1>
                    <p class="mt-1 text-sm text-gray-600">Transaction history for {{ $supplier->name }}</p>
                </div>
                <a
                    href="{{ route('suppliers.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Back to Suppliers
                </a>
            </div>
        </div>

        <!-- Supplier Summary Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Contact Person</h3>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $supplier->contact_person ?? '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Phone</h3>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $supplier->phone ?? '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Credit Terms</h3>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $supplier->credit_terms }} days</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Outstanding Balance</h3>
                    <p class="mt-1 text-lg font-semibold {{ $supplier->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₹{{ number_format($supplier->outstanding_balance, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
                <div class="flex-1">
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input
                        wire:model.live="startDate"
                        type="date"
                        id="startDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div class="flex-1 mt-3 sm:mt-0">
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input
                        wire:model.live="endDate"
                        type="date"
                        id="endDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Details
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit (Purchase)
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit (Payment)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction['date']->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction['type'] == 'GRN' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $transaction['type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction['reference'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $transaction['details'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $transaction['debit'] > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    @if($transaction['debit'] > 0)
                                        ₹{{ number_format($transaction['debit'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $transaction['credit'] > 0 ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                    @if($transaction['credit'] > 0)
                                        ₹{{ number_format($transaction['credit'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No transactions found for the selected date range.
                                </td>
                            </tr>
                        @endforelse

                        @if($transactions->count() > 0)
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="4" class="px-6 py-4 text-right text-sm text-gray-900">
                                    Total:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    ₹{{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    ₹{{ number_format($totalCredit, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="4" class="px-6 py-4 text-right text-sm text-gray-900">
                                    Outstanding Balance:
                                </td>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-right {{ ($totalDebit - $totalCredit) > 0 ? 'text-red-700' : 'text-green-700' }}">
                                    ₹{{ number_format($totalDebit - $totalCredit, 2) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
