<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Stock Inventory</h1>
            <p class="mt-1 text-sm text-gray-600">View and manage all stock batches with detailed information</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Batches</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($totalBatches) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active</dt>
                                <dd class="text-lg font-semibold text-green-900">{{ number_format($activeBatches) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Expiring Soon</dt>
                                <dd class="text-lg font-semibold text-yellow-900">{{ number_format($expiringBatches) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Expired</dt>
                                <dd class="text-lg font-semibold text-red-900">{{ number_format($expiredBatches) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Value</dt>
                                <dd class="text-lg font-semibold text-blue-900">Rs. {{ number_format($totalValue, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" id="search"
                            placeholder="Product name, SKU, barcode..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select wire:model.live="categoryFilter" id="category"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model.live="statusFilter" id="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="expiring_soon">Expiring Soon</option>
                            <option value="expired">Expired</option>
                            <option value="low_stock">Low Stock</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" wire:model.live="startDate" id="start_date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" wire:model.live="endDate" id="end_date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Min Price (Rs.)</label>
                        <input type="number" wire:model.live.debounce.300ms="minPrice" id="min_price"
                            placeholder="0.00" step="0.01"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Max Price (Rs.)</label>
                        <input type="number" wire:model.live.debounce.300ms="maxPrice" id="max_price"
                            placeholder="0.00" step="0.01"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Reset Button -->
                    <div class="flex items-end">
                        <button wire:click="resetFilters" type="button"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Batches Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Info</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($batches as $batch)
                            @php
                                $status = $this->getStockStatus($batch);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <!-- Product -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900">{{ $batch->product->name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $batch->product->sku }}</div>
                                        @if($batch->product->barcode)
                                            <div class="text-xs text-gray-500">Barcode: {{ $batch->product->barcode }}</div>
                                        @endif
                                        @if($batch->product->category)
                                            <div class="text-xs text-gray-500">{{ $batch->product->category->name }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Batch Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        @if($batch->batch_number)
                                            <div class="text-sm text-gray-900">{{ $batch->batch_number }}</div>
                                        @else
                                            <div class="text-sm text-gray-400 italic">No batch number</div>
                                        @endif
                                        <div class="text-xs text-gray-500">ID: #{{ $batch->id }}</div>
                                    </div>
                                </td>

                                <!-- Dates -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="text-xs">
                                            <span class="text-gray-500">Received:</span>
                                            <span class="text-gray-900">{{ $batch->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if($batch->manufacturing_date)
                                            <div class="text-xs">
                                                <span class="text-gray-500">Mfg:</span>
                                                <span class="text-gray-900">{{ \Carbon\Carbon::parse($batch->manufacturing_date)->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                        @if($batch->expiry_date)
                                            <div class="text-xs">
                                                <span class="text-gray-500">Exp:</span>
                                                <span class="text-gray-900">{{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Quantity -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        @if($batch->product->has_packaging && $batch->product->packaging)
                                            @php
                                                $totalPieces = $batch->remaining_quantity * $batch->product->packaging->pieces_per_package;
                                            @endphp
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ number_format($batch->remaining_quantity) }} {{ $batch->product->base_unit }}
                                                <span class="text-gray-600">({{ number_format($totalPieces) }} pieces)</span>
                                            </div>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">{{ number_format($batch->remaining_quantity) }} {{ $batch->product->base_unit }}</div>
                                        @endif
                                        @if($batch->product->has_packaging && $batch->product->packaging)
                                            @php
                                                $initialPieces = $batch->quantity * $batch->product->packaging->pieces_per_package;
                                            @endphp
                                            <div class="text-xs text-gray-500">
                                                Initial: {{ number_format($batch->quantity) }} ({{ number_format($initialPieces) }} pieces)
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-500">Initial: {{ number_format($batch->quantity) }}</div>
                                        @endif
                                        @if($batch->product->reorder_level && $batch->remaining_quantity <= $batch->product->reorder_level)
                                            <div class="text-xs text-orange-600 font-medium">Below reorder level</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Pricing -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="text-xs">
                                            <span class="text-gray-500">Cost:</span>
                                            <span class="text-gray-900 font-medium">Rs. {{ number_format($batch->unit_cost, 2) }}</span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-500">Min:</span>
                                            <span class="text-gray-900">Rs. {{ number_format($batch->min_selling_price, 2) }}</span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-500">Max:</span>
                                            <span class="text-gray-900">Rs. {{ number_format($batch->max_selling_price, 2) }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Value: Rs. {{ number_format($batch->remaining_quantity * $batch->unit_cost, 2) }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No stock batches found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($batches->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>

        <!-- Results Summary -->
        <div class="mt-4 text-sm text-gray-600 text-center">
            Showing {{ $batches->firstItem() ?? 0 }} to {{ $batches->lastItem() ?? 0 }} of {{ $batches->total() }} batches
        </div>
    </div>
</div>
