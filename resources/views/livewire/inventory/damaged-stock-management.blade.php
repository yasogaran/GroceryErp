<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Damaged Stock Management</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Products with Damage</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total_products_with_damage'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Damaged Quantity</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_damaged_quantity'], 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Damaged Stock Value</p>
                    <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_damaged_stock_value'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Search by product name or SKU..."
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter</label>
                <label class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        wire:model.live="showOnlyDamaged"
                        class="form-checkbox h-5 w-5 text-blue-600"
                    >
                    <span>Show only products with damaged stock</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">SKU</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Category</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Current Stock</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Damaged Stock</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Damage Value</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">{{ $product->name }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $product->sku }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $product->category?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-right">{{ number_format($product->current_stock_quantity, 0) }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="font-semibold {{ $product->damaged_stock_quantity > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ number_format($product->damaged_stock_quantity, 0) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-medium">
                                Rs. {{ number_format($product->damaged_stock_quantity * $product->max_selling_price, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button
                                        wire:click="openMarkDamagedModal({{ $product->id }})"
                                        class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-1 rounded text-sm"
                                        title="Mark Stock as Damaged"
                                    >
                                        Mark Damaged
                                    </button>
                                    @if($product->damaged_stock_quantity > 0)
                                        <button
                                            wire:click="openWriteOffModal({{ $product->id }})"
                                            class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded text-sm"
                                            title="Write-off Damaged Stock"
                                        >
                                            Write-off
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Mark as Damaged Modal -->
    @if($showMarkDamagedModal && $selectedProduct)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6"
                x-data
                @click.away="$wire.closeMarkDamagedModal()"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Mark Stock as Damaged</h3>
                    <button
                        wire:click="closeMarkDamagedModal"
                        class="text-gray-400 hover:text-gray-600"
                        type="button"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Product</p>
                    <p class="font-medium">{{ $selectedProduct->name }}</p>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Available Stock</p>
                    <p class="font-medium text-lg">{{ number_format($selectedProduct->current_stock_quantity, 0) }} pieces</p>
                </div>

                <!-- Batch Selection -->
                @if(count($availableBatches) > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Batch <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
                            @foreach($availableBatches as $batch)
                                <label class="block p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $selectedBatchId == $batch['stock_movement_id'] ? 'border-orange-500 bg-orange-50' : 'border-gray-300' }}">
                                    <div class="flex items-start">
                                        <input
                                            type="radio"
                                            wire:model.live="selectedBatchId"
                                            value="{{ $batch['stock_movement_id'] }}"
                                            class="form-radio h-4 w-4 text-orange-600 mt-1"
                                        >
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-gray-900">
                                                    Batch: {{ $batch['batch_number'] ?? 'N/A' }}
                                                </span>
                                                <span class="text-sm font-bold text-orange-600">
                                                    {{ number_format($batch['remaining_quantity'], 0) }} pcs
                                                </span>
                                            </div>

                                            <!-- Pricing and supplier information -->
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                @if(isset($batch['supplier_name']))
                                                    <div>
                                                        <span class="text-gray-600">Supplier:</span>
                                                        <span class="text-gray-900 font-medium">{{ $batch['supplier_name'] }}</span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="text-gray-600">Unit Cost:</span>
                                                    <span class="text-gray-900 font-medium">Rs. {{ number_format($batch['unit_cost'], 2) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Min Price:</span>
                                                    <span class="text-gray-900 font-medium">Rs. {{ number_format($batch['min_selling_price'], 2) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Max Price:</span>
                                                    <span class="text-gray-900 font-medium">Rs. {{ number_format($batch['max_selling_price'], 2) }}</span>
                                                </div>
                                            </div>

                                            <div class="text-xs text-gray-500 mt-2">
                                                @if(isset($batch['manufacturing_date']))
                                                    <span>Mfg: {{ \Carbon\Carbon::parse($batch['manufacturing_date'])->format('M d, Y') }}</span>
                                                @endif
                                                @if(isset($batch['expiry_date']))
                                                    <span class="ml-2">Exp: {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M d, Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedBatchId')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity to Mark as Damaged <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model.defer="damageQuantity"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        min="1"
                        max="{{ count($availableBatches) > 0 && $selectedBatchId ? (collect($availableBatches)->firstWhere('stock_movement_id', $selectedBatchId)['remaining_quantity'] ?? $selectedProduct->current_stock_quantity) : $selectedProduct->current_stock_quantity }}"
                        step="1"
                        placeholder="Enter quantity"
                    >
                    @error('damageQuantity')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        wire:model.defer="damageReason"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Enter reason for marking as damaged..."
                    ></textarea>
                    @error('damageReason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex space-x-3">
                    <button
                        wire:click="closeMarkDamagedModal"
                        type="button"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="markAsDamaged"
                        type="button"
                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="markAsDamaged">Confirm</span>
                        <span wire:loading wire:target="markAsDamaged">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Write-off Modal -->
    @if($showWriteOffModal && $selectedProduct)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6"
                x-data
                @click.away="$wire.closeWriteOffModal()"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Write-off Damaged Stock</h3>
                    <button
                        wire:click="closeWriteOffModal"
                        class="text-gray-400 hover:text-gray-600"
                        type="button"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-800">
                        <strong>Warning:</strong> This action will permanently remove the damaged stock from your inventory. This cannot be undone.
                    </p>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Product</p>
                    <p class="font-medium">{{ $selectedProduct->name }}</p>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Available Damaged Stock</p>
                    <p class="font-medium text-lg text-red-600">{{ number_format($selectedProduct->damaged_stock_quantity, 0) }} pieces</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity to Write-off <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model.defer="writeOffQuantity"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        min="1"
                        max="{{ $selectedProduct->damaged_stock_quantity }}"
                        step="1"
                        placeholder="Enter quantity"
                    >
                    @error('writeOffQuantity')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        wire:model.defer="writeOffReason"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Enter reason for write-off..."
                    ></textarea>
                    @error('writeOffReason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex space-x-3">
                    <button
                        wire:click="closeWriteOffModal"
                        type="button"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="writeOff"
                        type="button"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="writeOff">Confirm Write-off</span>
                        <span wire:loading wire:target="writeOff">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
