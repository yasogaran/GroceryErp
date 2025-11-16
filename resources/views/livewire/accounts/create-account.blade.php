<form wire:submit="save">
    <div class="space-y-4">
        <!-- Account Code -->
        <div>
            <label for="account_code" class="block text-sm font-medium text-gray-700">Account Code <span class="text-red-500">*</span></label>
            <input
                wire:model="account_code"
                type="text"
                id="account_code"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm uppercase"
                placeholder="e.g., ACC001, REVENUE1"
            >
            <p class="mt-1 text-xs text-gray-500">Unique code to identify this account. Will be converted to uppercase.</p>
            @error('account_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Account Name -->
        <div>
            <label for="account_name" class="block text-sm font-medium text-gray-700">Account Name <span class="text-red-500">*</span></label>
            <input
                wire:model="account_name"
                type="text"
                id="account_name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="e.g., Office Supplies, Rent Expense"
            >
            @error('account_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Account Type -->
        <div>
            <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type <span class="text-red-500">*</span></label>
            <select
                wire:model="account_type"
                id="account_type"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                @foreach($accountTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Select the type of account based on accounting principles.</p>
            @error('account_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Parent Account -->
        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Account</label>
            <select
                wire:model="parent_id"
                id="parent_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">None (Main Account)</option>
                @foreach($parentAccounts as $type => $accounts)
                    <optgroup label="{{ ucfirst($type) }}">
                        @foreach($accounts as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->account_code }} - {{ $parent->account_name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Optional: Select a parent account to create a sub-account.</p>
            @error('parent_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
            <input
                wire:model="is_active"
                type="checkbox"
                id="is_active"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active
            </label>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="mt-6 flex justify-end space-x-3">
        <button
            type="button"
            wire:click="$dispatch('close-modal')"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Cancel
        </button>
        <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Create Account
        </button>
    </div>
</form>
