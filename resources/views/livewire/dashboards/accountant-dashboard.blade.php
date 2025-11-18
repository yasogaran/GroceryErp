<div>
    <x-slot name="header">
        Accountant Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-emerald-100">
                Financial overview and accounting management
            </p>
        </div>

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Today's Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todayRevenue, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Month's Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Month's Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthRevenue, 2) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Expenses -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todayExpenses, 2) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Month's Expenses -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Month's Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthExpenses, 2) }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding & Pending -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Supplier Outstanding -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Supplier Outstanding</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($supplierOutstanding, 2) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Customer Outstanding -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Customer Outstanding</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($customerOutstanding, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Journal Entries -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Journals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingJournalEntries) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Unpaid GRNs -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-pink-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unpaid GRNs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($unpaidGRNs) }}</p>
                    </div>
                    <div class="p-3 bg-pink-100 rounded-full">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: Unpaid GRNs and Recent Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Unpaid GRNs List -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Unpaid GRNs</h3>
                @if($unpaidGRNsList->count() > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($unpaidGRNsList as $grn)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $grn->grn_number }}</p>
                                    <p class="text-sm text-gray-600">{{ $grn->supplier->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $grn->grn_date->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rs. {{ number_format($grn->total_amount, 2) }}</p>
                                    @if($grn->payment_status === 'partially_paid')
                                        <p class="text-xs text-yellow-600">Partially Paid</p>
                                    @else
                                        <p class="text-xs text-red-600">Unpaid</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">All GRNs are paid!</p>
                @endif
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Supplier Payments</h3>
                @if($recentPayments->count() > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($recentPayments as $payment)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $payment->supplier->name }}</p>
                                    <p class="text-sm text-gray-600">{{ ucfirst($payment->payment_mode) }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600">Rs. {{ number_format($payment->amount, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No recent payments</p>
                @endif
            </div>
        </div>

        <!-- Recent Journal Entries -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Journal Entries</h3>
            @if($recentJournalEntries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entry Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Debit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentJournalEntries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $entry->entry_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($entry->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($entry->entry_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs. {{ number_format($entry->total_debit, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs. {{ number_format($entry->total_credit, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $entry->getStatusBadgeClass() }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->entry_date->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No recent journal entries</p>
            @endif
        </div>

        <!-- Net Profit/Loss (Month) -->
        @php
            $netProfit = $monthRevenue - $monthExpenses;
        @endphp
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Net Profit/Loss (This Month)</h3>
                    <p class="text-sm text-gray-600 mt-1">Revenue - Expenses</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format(abs($netProfit), 2) }}
                    </p>
                    <p class="text-sm {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
