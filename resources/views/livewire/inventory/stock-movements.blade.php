<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Stock Movements</h1>
            <p class="mt-1 text-sm text-gray-600">View all inventory stock movements and track product flow.</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search by product name or SKU..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <!-- Product Filter -->
                <div>
                    <select
                        wire:model.live="productFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <select
                        wire:model.live="typeFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Types</option>
                        @foreach($movementTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="start_date" class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                    <input
                        wire:model.live="startDate"
                        type="date"
                        id="start_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                    <input
                        wire:model.live="endDate"
                        type="date"
                        id="end_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
            </div>
        </div>

        <!-- Stock Movements Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performed By
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Notes
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ $movement->product->sku }}</div>
                                    @if($movement->product->category)
                                        <div class="text-xs text-gray-500">{{ $movement->product->category->name }}</div>
                                    @endif
                                    @if($movement->supplier_name)
                                        <div class="text-xs text-blue-600 font-medium">Supplier: {{ $movement->supplier_name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($movement->movement_type === 'in') bg-green-100 text-green-800
                                        @elseif($movement->movement_type === 'out') bg-blue-100 text-blue-800
                                        @elseif($movement->movement_type === 'damage') bg-red-100 text-red-800
                                        @elseif($movement->movement_type === 'adjustment') bg-yellow-100 text-yellow-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                        {{ $movement->getMovementTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium {{ $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($movement->reference_type)
                                        <div class="text-sm text-gray-900">{{ ucfirst($movement->reference_type) }}</div>
                                        @if($movement->reference_id)
                                            <div class="text-xs text-gray-500">#{{ $movement->reference_id }}</div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-500">—</span>
                                    @endif
                                    @if($movement->batch_number)
                                        <div class="text-xs text-gray-500">Batch: {{ $movement->batch_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->performedBy->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($movement->performedBy->role) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $movement->notes }}">
                                        {{ $movement->notes ?: '—' }}
                                    </div>
                                    @if($movement->expiry_date)
                                        <div class="text-xs text-gray-500">Exp: {{ $movement->expiry_date->format('M d, Y') }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="mt-2 text-sm">No stock movements found.</p>
                                        @if($search || $productFilter || $typeFilter || $startDate || $endDate)
                                            <p class="mt-1 text-sm">Try adjusting your search or filter criteria.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>
