<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Shift Management</h1>
            <p class="mt-1 text-sm text-gray-600">View and manage cashier shifts and track sales performance.</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="flex-1 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Search by cashier name..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Cashier Filter -->
                        <div class="w-full sm:w-48">
                            <select
                                wire:model.live="cashierFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">All Cashiers</option>
                                @foreach($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-40">
                            <select
                                wire:model.live="statusFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="all">All Status</option>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <!-- Verification Filter -->
                        <div class="w-full sm:w-40">
                            <select
                                wire:model.live="verificationFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="all">All Verified</option>
                                <option value="verified">Verified</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="flex flex-col sm:flex-row sm:space-x-3 space-y-3 sm:space-y-0">
                    <div class="flex-1">
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input
                            wire:model.live="startDate"
                            type="date"
                            id="startDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div class="flex-1">
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
        </div>

        <!-- Shifts Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cashier
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Shift Start
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Shift End
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Opening Cash
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Sales
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transactions
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cash Variance
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $shift->cashier->name }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($shift->cashier->role) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $shift->shift_start->format('Y-m-d') }}<br>
                                    <span class="text-xs text-gray-500">{{ $shift->shift_start->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($shift->shift_end)
                                        {{ $shift->shift_end->format('Y-m-d') }}<br>
                                        <span class="text-xs text-gray-500">{{ $shift->shift_end->format('h:i A') }}</span>
                                        @if($shift->duration)
                                            <div class="text-xs text-blue-600 mt-1">{{ $shift->duration }}</div>
                                        @endif
                                    @else
                                        <span class="text-green-600 font-medium">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                                    {{ settings('currency_symbol', 'Rs.') }} {{ number_format($shift->opening_cash, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="text-gray-900 font-medium">{{ settings('currency_symbol', 'Rs.') }} {{ number_format($shift->total_sales, 2) }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Cash: {{ settings('currency_symbol', 'Rs.') }} {{ number_format($shift->total_cash_sales, 2) }}<br>
                                        Bank: {{ settings('currency_symbol', 'Rs.') }} {{ number_format($shift->total_bank_sales, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $shift->total_transactions }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if($shift->cash_variance !== null)
                                        <span class="font-medium {{ $shift->cash_variance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $shift->cash_variance >= 0 ? '+' : '' }}{{ settings('currency_symbol', 'Rs.') }} {{ number_format($shift->cash_variance, 2) }}
                                        </span>
                                        @if($shift->variance_notes)
                                            <div class="text-xs text-gray-500 mt-1" title="{{ $shift->variance_notes }}">
                                                Has notes
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $shift->is_open ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $shift->is_open ? 'Open' : 'Closed' }}
                                        </span>
                                        @if(!$shift->is_open)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $shift->is_verified ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $shift->is_verified ? 'Verified' : 'Unverified' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No shifts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $shifts->links() }}
            </div>
        </div>
    </div>
</div>
