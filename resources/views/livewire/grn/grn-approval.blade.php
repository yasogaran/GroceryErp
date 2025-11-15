<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">GRN Details</h1>
                    <p class="mt-1 text-sm text-gray-600">View and approve goods receipt note</p>
                </div>
                <div class="flex space-x-3">
                    <a
                        href="{{ route('grn.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Back to GRNs
                    </a>
                    @if($grn->status === 'draft')
                        <button
                            wire:click="confirmApproval"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Approve GRN
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- GRN Information -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">GRN Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">GRN Number</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->grn_number }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Supplier</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->supplier->name }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">GRN Date</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->grn_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                    <p class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $grn->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($grn->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Amount</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($grn->total_amount, 2) }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Created By</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->creator->name }}</p>
                </div>
                @if($grn->status === 'approved')
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Approved By</h4>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->approver->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Approved At</h4>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grn->approved_at->format('Y-m-d H:i') }}</p>
                    </div>
                @endif
                @if($grn->notes)
                    <div class="md:col-span-3">
                        <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                        <p class="mt-1 text-gray-900">{{ $grn->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Items ({{ $grn->items->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Boxes
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pieces
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Batch/Expiry
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($grn->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ $item->product->name }}</div>
                                    <div class="text-gray-500">{{ $item->product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">
                                    {{ $item->received_boxes }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">
                                    {{ number_format($item->received_pieces, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">
                                    ₹{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                    ₹{{ number_format($item->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($item->batch_number)
                                        <div>Batch: {{ $item->batch_number }}</div>
                                    @endif
                                    @if($item->manufacturing_date)
                                        <div>Mfg: {{ $item->manufacturing_date->format('Y-m-d') }}</div>
                                    @endif
                                    @if($item->expiry_date)
                                        <div>Exp: {{ $item->expiry_date->format('Y-m-d') }}</div>
                                    @endif
                                    @if($item->notes)
                                        <div class="mt-1 text-xs">{{ $item->notes }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="4" class="px-6 py-4 text-right text-sm text-gray-900">
                                Grand Total:
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">
                                ₹{{ number_format($grn->total_amount, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approval Confirmation Modal -->
    @if($showApprovalModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Approve GRN
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to approve this GRN? This will:
                                    </p>
                                    <ul class="mt-2 text-sm text-gray-500 list-disc list-inside">
                                        <li>Increase stock for all items</li>
                                        <li>Update supplier outstanding balance</li>
                                        <li>Create stock movement records</li>
                                        <li>Mark GRN as approved (cannot be edited)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="approve"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Approve
                        </button>
                        <button
                            wire:click="cancelApproval"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
