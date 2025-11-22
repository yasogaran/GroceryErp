<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Journal Entries Report</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Complete journal entries with advanced filters</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Entries</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_entries']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                {{ number_format($stats['posted_entries']) }} posted, {{ number_format($stats['draft_entries']) }} draft
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Debits</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($stats['total_debits'], 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Credits</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">Rs. {{ number_format($stats['total_credits'], 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Balance Check</div>
            <div class="text-2xl font-bold {{ abs($stats['total_debits'] - $stats['total_credits']) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                {{ abs($stats['total_debits'] - $stats['total_credits']) < 0.01 ? 'Balanced' : 'Unbalanced' }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                Diff: Rs. {{ number_format(abs($stats['total_debits'] - $stats['total_credits']), 2) }}
            </div>
        </div>
    </div>

    <!-- Filters and Export -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="Reference or description..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select wire:model.live="statusFilter"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="all">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="posted">Posted</option>
                        <option value="reversed">Reversed</option>
                    </select>
                </div>

                <!-- Account Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account</label>
                    <select wire:model.live="accountFilter"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Type</label>
                    <select wire:model.live="reportType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="summary">Summary</option>
                        <option value="detailed">Detailed (All Lines)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $entries->count() }} of {{ $entries->total() }} entries
            </div>
            <div class="flex gap-2">
                <button wire:click="exportToExcel"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPdf"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th wire:click="sortBy('entry_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            Date
                            @if($sortBy === 'entry_date')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('reference')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            Reference
                            @if($sortBy === 'reference')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Lines
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total Debit
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total Credit
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Created By
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $entry->entry_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                {{ $entry->reference }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ $entry->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                <details class="cursor-pointer">
                                    <summary>{{ $entry->lines->count() }} lines</summary>
                                    <div class="mt-2 space-y-1 text-xs">
                                        @foreach($entry->lines as $line)
                                            <div class="flex justify-between">
                                                <span>{{ $line->account?->account_code }} - {{ $line->account?->account_name }}</span>
                                                <span>
                                                    @if($line->debit > 0)
                                                        <span class="text-green-600">Dr: {{ number_format($line->debit, 2) }}</span>
                                                    @else
                                                        <span class="text-red-600">Cr: {{ number_format($line->credit, 2) }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">
                                Rs. {{ number_format($entry->lines->sum('debit'), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">
                                Rs. {{ number_format($entry->lines->sum('credit'), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($entry->status === 'posted')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Posted
                                    </span>
                                @elseif($entry->status === 'draft')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Draft
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Reversed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $entry->creator?->name ?? 'N/A' }}
                                <div class="text-xs text-gray-500">{{ $entry->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No journal entries found for the selected filters
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                            Totals:
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-green-600 dark:text-green-400">
                            Rs. {{ number_format($entries->sum(fn($e) => $e->lines->sum('debit')), 2) }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-red-600 dark:text-red-400">
                            Rs. {{ number_format($entries->sum(fn($e) => $e->lines->sum('credit')), 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $entries->links() }}
        </div>
    </div>
</div>
