<div>
    <x-slot name="header">
        Activity Logs
    </x-slot>

    <div class="space-y-6">
        <!-- Filters Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Filters</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search logs..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <!-- Filter by User -->
                <div>
                    <label for="filterUser" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <input
                        type="text"
                        id="filterUser"
                        wire:model.live.debounce.300ms="filterUser"
                        placeholder="Filter by user..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <!-- Filter by Action -->
                <div>
                    <label for="filterAction" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select
                        id="filterAction"
                        wire:model.live="filterAction"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Actions</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="logged in">Logged In</option>
                        <option value="logged out">Logged Out</option>
                        <option value="failed login attempt">Failed Login</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input
                        type="date"
                        id="filterDateFrom"
                        wire:model.live="filterDateFrom"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="filterDateTo" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input
                        type="date"
                        id="filterDateTo"
                        wire:model.live="filterDateTo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
            </div>

            <!-- Reset Filters Button -->
            <div class="mt-4">
                <button
                    wire:click="resetFilters"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                >
                    Reset Filters
                </button>
                <span class="ml-4 text-sm text-gray-600">
                    Showing {{ $logs->count() }} of {{ $total }} total logs
                </span>
            </div>
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date/Time
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Module
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log['timestamp'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $log['user'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $actionColors = [
                                            'created' => 'bg-green-100 text-green-800',
                                            'updated' => 'bg-blue-100 text-blue-800',
                                            'deleted' => 'bg-red-100 text-red-800',
                                            'logged in' => 'bg-purple-100 text-purple-800',
                                            'logged out' => 'bg-gray-100 text-gray-800',
                                            'failed login attempt' => 'bg-yellow-100 text-yellow-800',
                                        ];
                                        $colorClass = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                        {{ ucfirst($log['action']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log['module'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ Str::limit($log['description'], 100) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium">No activity logs found</p>
                                        <p class="text-sm">Try adjusting your filters or perform some actions to generate logs.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($total > $perPage)
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ (($currentPage - 1) * $perPage) + 1 }}</span>
                            to
                            <span class="font-medium">{{ min($currentPage * $perPage, $total) }}</span>
                            of
                            <span class="font-medium">{{ $total }}</span>
                            results
                        </div>
                        <div class="flex space-x-2">
                            @if($currentPage > 1)
                                <button
                                    wire:click="previousPage"
                                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Previous
                                </button>
                            @endif

                            @if($currentPage < $lastPage)
                                <button
                                    wire:click="nextPage"
                                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Next
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-700">
                        Activity logs are automatically recorded for create, update, and delete operations on models, as well as authentication events.
                        Logs are stored in <code class="bg-blue-100 px-1 rounded">storage/logs/laravel.log</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>