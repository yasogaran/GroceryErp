<div>
    @if($showModal)
    <!-- Modal Overlay -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         x-data="{}"
         x-init="document.body.style.overflow = 'hidden'"
         x-on:click.self="$wire.closeModal()">

        <!-- Modal Content -->
        <div class="relative top-20 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">
                    Record Payment for {{ $grn->grn_number }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- GRN Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Supplier</p>
                        <p class="font-semibold text-gray-900">{{ $grn->supplier->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">GRN Date</p>
                        <p class="font-semibold text-gray-900">{{ $grn->grn_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Amount</p>
                        <p class="font-bold text-lg text-blue-600">₹{{ number_format($grn->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Credit Terms</p>
                        <p class="font-semibold text-gray-900">{{ $grn->supplier->credit_terms }} days</p>
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="space-y-4 mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Option
                </label>

                <!-- Full Payment -->
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50
                              {{ $payment_type === 'full' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                    <input type="radio"
                           wire:model.live="payment_type"
                           value="full"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3 flex-1">
                        <div class="font-medium text-gray-900">Pay Full Amount</div>
                        <div class="text-sm text-gray-600">₹{{ number_format($grn->total_amount, 2) }}</div>
                    </div>
                </label>

                <!-- Partial Payment -->
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50
                              {{ $payment_type === 'partial' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                    <input type="radio"
                           wire:model.live="payment_type"
                           value="partial"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3 flex-1">
                        <div class="font-medium text-gray-900">Pay Partial Amount</div>
                        <div class="text-sm text-gray-600">Enter custom amount</div>
                    </div>
                </label>

                <!-- Skip Payment -->
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50
                              {{ $payment_type === 'skip' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                    <input type="radio"
                           wire:model.live="payment_type"
                           value="skip"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3 flex-1">
                        <div class="font-medium text-gray-900">Pay Later</div>
                        <div class="text-sm text-gray-600">Record payment later from supplier page</div>
                    </div>
                </label>
            </div>

            <!-- Payment Form (shown when not skipping) -->
            @if($payment_type !== 'skip')
            <div class="space-y-4 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-3">Payment Details</h4>

                <!-- Payment Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           wire:model="payment_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           max="{{ now()->format('Y-m-d') }}">
                    @error('payment_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Payment Amount (editable only for partial) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                        <input type="number"
                               wire:model="payment_amount"
                               step="0.01"
                               min="0.01"
                               max="{{ $grn->total_amount }}"
                               {{ $payment_type === 'full' ? 'readonly' : '' }}
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $payment_type === 'full' ? 'bg-gray-100' : '' }}">
                    </div>
                    @error('payment_amount')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Payment Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Mode <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="payment_mode"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                    @error('payment_mode')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Bank Reference (shown only for bank transfer) -->
                @if($payment_mode === 'bank_transfer')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bank Reference
                    </label>
                    <input type="text"
                           wire:model="bank_reference"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter bank reference number">
                    @error('bank_reference')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <!-- Reference Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Reference Number
                    </label>
                    <input type="text"
                           wire:model="reference_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter reference number (optional)">
                    @error('reference_number')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea wire:model="notes"
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter any additional notes (optional)"></textarea>
                    @error('notes')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button"
                        wire:click="closeModal"
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="button"
                        wire:click="recordPayment"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($payment_type === 'skip')
                        Continue Without Payment
                    @else
                        Record Payment
                    @endif
                </button>
            </div>

        </div>
    </div>
    @endif
</div>
