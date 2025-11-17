<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Sale Returns History</h1>
        <a href="{{ route('returns.process') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + New Return
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Returns</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total_returns'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Refunded</p>
                    <p class="text-2xl font-bold text-gray-800">{{ format_currency($summary['total_refund_amount']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Cash Refunds</p>
                    <p class="text-xl font-bold text-gray-800">{{ format_currency($summary['cash_refunds']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Bank Refunds</p>
                    <p class="text-xl font-bold text-gray-800">{{ format_currency($summary['bank_refunds']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                >
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Search by return number, invoice, customer..."
                >
            </div>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Return #</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Invoice #</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Return Date</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Refund Amount</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Refund Mode</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Created By</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-blue-600">{{ $return->return_number }}</td>
                            <td class="py-3 px-4">{{ $return->originalSale->invoice_number }}</td>
                            <td class="py-3 px-4">{{ $return->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4">{{ $return->return_date->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4 text-right font-medium">{{ format_currency($return->total_refund_amount) }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($return->refund_mode === 'cash')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Cash
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Bank
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">{{ $return->creator->name }}</td>
                            <td class="py-3 px-4 text-center">
                                <button
                                    wire:click="viewDetails({{ $return->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-4 text-center text-gray-500">
                                No returns found for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            {{ $returns->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedReturn)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Return Details - {{ $selectedReturn->return_number }}</h3>
                    <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Return Information -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Return Number</p>
                            <p class="font-medium">{{ $selectedReturn->return_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Original Invoice</p>
                            <p class="font-medium">{{ $selectedReturn->originalSale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Return Date</p>
                            <p class="font-medium">{{ $selectedReturn->return_date->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Customer</p>
                            <p class="font-medium">{{ $selectedReturn->customer?->name ?? 'Walk-in' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Refund Mode</p>
                            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $selectedReturn->refund_mode)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created By</p>
                            <p class="font-medium">{{ $selectedReturn->creator->name }}</p>
                        </div>
                    </div>

                    @if($selectedReturn->reason)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Reason</p>
                            <p class="font-medium">{{ $selectedReturn->reason }}</p>
                        </div>
                    @endif
                </div>

                <!-- Return Items -->
                <div class="mb-4">
                    <h4 class="font-semibold mb-2">Returned Items</h4>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-2 px-3">Product</th>
                                <th class="text-right py-2 px-3">Quantity</th>
                                <th class="text-center py-2 px-3">Status</th>
                                <th class="text-right py-2 px-3">Refund Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($selectedReturn->items as $item)
                                <tr>
                                    <td class="py-2 px-3">{{ $item->product->name }}</td>
                                    <td class="py-2 px-3 text-right">{{ number_format($item->returned_quantity, 0) }}</td>
                                    <td class="py-2 px-3 text-center">
                                        @if($item->is_damaged)
                                            <span class="text-red-600 font-medium">Damaged</span>
                                        @else
                                            <span class="text-green-600 font-medium">Good</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right">{{ format_currency($item->refund_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="py-2 px-3 text-right font-semibold">Total Refund:</td>
                                <td class="py-2 px-3 text-right font-bold text-lg">
                                    {{ format_currency($selectedReturn->total_refund_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end">
                    <button
                        wire:click="closeDetailModal"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
