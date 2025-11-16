<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ $isEditMode ? 'Edit Supplier' : 'Create New Supplier' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $isEditMode ? 'Update supplier information' : 'Add a new supplier to the system' }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Supplier Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            id="name"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                        >
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700">
                            Contact Person
                        </label>
                        <input
                            wire:model="contact_person"
                            type="text"
                            id="contact_person"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        @error('contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Phone Number
                        </label>
                        <input
                            wire:model="phone"
                            type="text"
                            id="phone"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input
                            wire:model="email"
                            type="email"
                            id="email"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                        >
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">
                            City
                        </label>
                        <input
                            wire:model="city"
                            type="text"
                            id="city"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">
                            Address
                        </label>
                        <textarea
                            wire:model="address"
                            id="address"
                            rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        ></textarea>
                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Credit Terms -->
                    <div>
                        <label for="credit_terms" class="block text-sm font-medium text-gray-700">
                            Credit Terms (Days) <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="credit_terms"
                            id="credit_terms"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('credit_terms') border-red-500 @enderror"
                        >
                            <option value="0">Cash (0 days)</option>
                            <option value="7">7 days</option>
                            <option value="15">15 days</option>
                            <option value="30">30 days</option>
                            <option value="45">45 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                        </select>
                        @error('credit_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <div class="flex items-center h-5 mt-6">
                            <input
                                wire:model="is_active"
                                id="is_active"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 mt-6">
                            <label for="is_active" class="text-sm font-medium text-gray-700">
                                Active
                            </label>
                            <p class="text-xs text-gray-500">Enable this supplier for new purchases</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a
                        href="{{ route('suppliers.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ $isEditMode ? 'Update Supplier' : 'Create Supplier' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
