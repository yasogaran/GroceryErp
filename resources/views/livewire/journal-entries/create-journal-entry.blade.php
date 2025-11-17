<div>
    <form wire:submit.prevent="save">
        <!-- Entry Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Entry Date *</label>
                <input wire:model="entry_date" type="date" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('entry_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Entry Type *</label>
                <select wire:model="entry_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @foreach($entryTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('entry_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea wire:model="description" rows="2" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Journal Lines -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Journal Lines</h3>
                <button type="button" wire:click="addLine"
                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    + Add Line
                </button>
            </div>

            <div class="space-y-3">
                @foreach($lines as $index => $line)
                    <div class="border border-gray-200 rounded-lg p-3" wire:key="line-{{ $index }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Account *</label>
                                <select wire:model="lines.{{ $index }}.account_id" required
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $type => $accts)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($accts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error("lines.{$index}.account_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                <input wire:model="lines.{{ $index }}.description" type="text"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Debit</label>
                                <input wire:model="lines.{{ $index }}.debit" type="number" step="0.01" min="0"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                @error("lines.{$index}.debit") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Credit</label>
                                <input wire:model="lines.{{ $index }}.credit" type="number" step="0.01" min="0"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                @error("lines.{$index}.credit") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                @if(count($lines) > 2)
                                    <button type="button" wire:click="removeLine({{ $index }})"
                                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                        Remove
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm font-medium text-gray-500">Total Debit</div>
                    <div class="text-lg font-bold text-gray-900">₹{{ number_format($totalDebit, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Total Credit</div>
                    <div class="text-lg font-bold text-gray-900">₹{{ number_format($totalCredit, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Difference</div>
                    <div class="text-lg font-bold {{ $this->isBalanced() ? 'text-green-600' : 'text-red-600' }}">
                        ₹{{ number_format(abs($totalDebit - $totalCredit), 2) }}
                    </div>
                </div>
            </div>
            @if($this->isBalanced())
                <div class="mt-3 text-center text-sm text-green-600 font-medium">
                    ✓ Entry is balanced
                </div>
            @else
                <div class="mt-3 text-center text-sm text-red-600 font-medium">
                    ✗ Entry is not balanced
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-2">
            <button type="button" wire:click="$dispatch('entry-created')"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </button>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save as Draft
            </button>
            <button type="button" wire:click="saveAndPost"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Save & Post
            </button>
        </div>
    </form>
</div>
