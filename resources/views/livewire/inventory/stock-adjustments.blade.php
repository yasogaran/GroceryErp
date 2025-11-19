<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Stock Adjustments</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['pending_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['approved_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['rejected_count'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="setActiveTab('create')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'create' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Create Adjustment
                </button>
                <button
                    wire:click="setActiveTab('pending')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Pending ({{ $summary['pending_count'] }})
                </button>
                <button
                    wire:click="setActiveTab('approved')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'approved' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Approved
                </button>
                <button
                    wire:click="setActiveTab('rejected')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'rejected' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Rejected
                </button>
            </nav>
        </div>
    </div>

    <!-- Create Adjustment Form -->
    @if($activeTab === 'create')
        <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
            <h2 class="text-xl font-bold mb-6">Create Stock Adjustment Request</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Product <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.live="productId"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                >
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (Current Stock: {{ number_format($product->current_stock_quantity, 0) }})
                        </option>
                    @endforeach
                </select>
                @error('productId')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Adjustment Type <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="adjustmentType"
                            value="increase"
                            class="form-radio h-5 w-5 text-blue-600"
                        >
                        <span class="ml-2">Increase Stock</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            wire:model.live="adjustmentType"
                            value="decrease"
                            class="form-radio h-5 w-5 text-blue-600"
                        >
                        <span class="ml-2">Decrease Stock</span>
                    </label>
                </div>
                @error('adjustmentType')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Batch Selection (for decrease adjustments with multiple batches) -->
            @if($adjustmentType === 'decrease' && count($availableBatches) > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Batch <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($availableBatches as $batch)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $selectedBatchId == $batch['stock_movement_id'] ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                                <input
                                    type="radio"
                                    wire:model.live="selectedBatchId"
                                    value="{{ $batch['stock_movement_id'] }}"
                                    class="form-radio h-4 w-4 text-blue-600"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-900">
                                            Batch: {{ $batch['batch_number'] ?? 'N/A' }}
                                        </span>
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ number_format($batch['remaining_quantity'], 0) }} pcs
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if(isset($batch['expiry_date']))
                                            Expiry: {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M d, Y') }}
                                        @endif
                                        @if(isset($batch['manufacturing_date']))
                                            | Mfg: {{ \Carbon\Carbon::parse($batch['manufacturing_date'])->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    wire:model="quantity"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    min="0.01"
                    step="1"
                    placeholder="Enter quantity"
                >
                @error('quantity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model="reason"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                >
                    <option value="counting_error">Counting Error</option>
                    <option value="theft">Theft</option>
                    <option value="sampling">Sampling</option>
                    <option value="expiry">Expiry</option>
                    <option value="other">Other</option>
                </select>
                @error('reason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes
                </label>
                <textarea
                    wire:model="notes"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Enter additional details..."
                ></textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                wire:click="createAdjustment"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Submit Adjustment Request</span>
                <span wire:loading>Submitting...</span>
            </button>
        </div>
    @endif

    <!-- Adjustments List -->
    @if(in_array($activeTab, ['pending', 'approved', 'rejected']))
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Search -->
            <div class="p-4 border-b border-gray-200">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Search by product name or SKU..."
                >
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Type</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Reason</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Requested By</th>
                            @if(in_array($activeTab, ['approved', 'rejected']))
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">{{ ucfirst($activeTab) }} By</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">{{ ucfirst($activeTab) }} At</th>
                            @endif
                            @if($activeTab === 'pending')
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($adjustments as $adjustment)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $adjustment->product->name }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($adjustment->adjustment_type === 'increase')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            + Increase
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            - Decrease
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-medium">{{ number_format($adjustment->quantity, 0) }}</td>
                                <td class="py-3 px-4">
                                    {{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}
                                    @if($adjustment->notes)
                                        <br><span class="text-sm text-gray-600">{{ $adjustment->notes }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $adjustment->creator->name }}</td>
                                @if(in_array($activeTab, ['approved', 'rejected']))
                                    <td class="py-3 px-4">{{ $adjustment->approver?->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $adjustment->approved_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                @endif
                                @if($activeTab === 'pending')
                                    <td class="py-3 px-4">
                                        <div class="flex items-center justify-center space-x-2">
                                            @if($adjustment->created_by !== auth()->id())
                                                <button
                                                    wire:click="approveAdjustment({{ $adjustment->id }})"
                                                    wire:confirm="Are you sure you want to approve this adjustment?"
                                                    class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded text-sm"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    wire:click="rejectAdjustment({{ $adjustment->id }})"
                                                    wire:confirm="Are you sure you want to reject this adjustment?"
                                                    class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded text-sm"
                                                >
                                                    Reject
                                                </button>
                                            @else
                                                <span class="text-sm text-gray-500 italic">Your request</span>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 px-4 text-center text-gray-500">
                                    No {{ $activeTab }} adjustments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($adjustments instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
