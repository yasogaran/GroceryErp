<form wire:submit="update">
    <div class="space-y-4">
        @if($is_system_account)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            This is a system account and cannot be edited. Only the active status can be changed.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Account Code -->
        <div>
            <label for="account_code" class="block text-sm font-medium text-gray-700">Account Code <span class="text-red-500">*</span></label>
            <input
                wire:model="account_code"
                type="text"
                id="account_code"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm uppercase"
                placeholder="e.g., ACC001, REVENUE1"
                @if($is_system_account) disabled @endif
            >
            <p class="mt-1 text-xs text-gray-500">Unique code to identify this account.</p>
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
                @if($is_system_account) disabled @endif
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
                @if($is_system_account) disabled @endif
            >
                @foreach($accountTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('account_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Parent Account -->
        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Account</label>
            <select
                wire:model="parent_id"
                id="parent_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @if($is_system_account) disabled @endif
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
            @if($is_system_account) disabled @endif
        >
            Update Account
        </button>
    </div>
</form>
