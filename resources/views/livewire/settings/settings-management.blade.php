<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">System Settings</h2>
                    <p class="text-gray-600 mt-1">Manage your grocery shop settings and preferences</p>
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

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="save">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button type="button" wire:click="setActiveTab('general')"
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-150
                                    {{ $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                General
                            </button>
                            <button type="button" wire:click="setActiveTab('inventory')"
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-150
                                    {{ $activeTab === 'inventory' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Inventory
                            </button>
                            <button type="button" wire:click="setActiveTab('pos')"
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-150
                                    {{ $activeTab === 'pos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                POS
                            </button>
                            <button type="button" wire:click="setActiveTab('receipt')"
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-150
                                    {{ $activeTab === 'receipt' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Receipt
                            </button>
                            <button type="button" wire:click="setActiveTab('accounting')"
                                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-150
                                    {{ $activeTab === 'accounting' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Accounting
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="space-y-6">
                        <!-- General Settings -->
                        @if($activeTab === 'general')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-2">Shop Name *</label>
                                    <input type="text" id="shop_name" wire:model="settings.shop_name"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('settings.shop_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="shop_email" class="block text-sm font-medium text-gray-700 mb-2">Shop Email *</label>
                                    <input type="email" id="shop_email" wire:model="settings.shop_email"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('settings.shop_email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="shop_phone" class="block text-sm font-medium text-gray-700 mb-2">Shop Phone *</label>
                                    <input type="text" id="shop_phone" wire:model="settings.shop_phone"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('settings.shop_phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="shop_address" class="block text-sm font-medium text-gray-700 mb-2">Shop Address *</label>
                                    <textarea id="shop_address" wire:model="settings.shop_address" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                    @error('settings.shop_address') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Shop Logo</label>

                                    @if($currentLogo)
                                        <div class="mb-4">
                                            <img src="{{ asset('storage/' . $currentLogo) }}" alt="Shop Logo" class="h-32 object-contain border border-gray-300 rounded-lg p-2">
                                            <button type="button" wire:click="removeLogo" class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium">
                                                Remove Logo
                                            </button>
                                        </div>
                                    @endif

                                    <input type="file" wire:model="logo" accept="image/*"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('logo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                                    @if($logo)
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-600">Preview:</p>
                                            <img src="{{ $logo->temporaryUrl() }}" alt="Logo Preview" class="h-32 object-contain border border-gray-300 rounded-lg p-2 mt-1">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Inventory Settings -->
                        @if($activeTab === 'inventory')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-2">Low Stock Threshold *</label>
                                    <input type="number" id="low_stock_threshold" wire:model="settings.low_stock_threshold" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-sm text-gray-500 mt-1">Minimum stock level before low stock alert is triggered</p>
                                    @error('settings.low_stock_threshold') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="expiry_alert_days" class="block text-sm font-medium text-gray-700 mb-2">Expiry Alert Days *</label>
                                    <input type="number" id="expiry_alert_days" wire:model="settings.expiry_alert_days" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-sm text-gray-500 mt-1">Number of days before expiry to show alert</p>
                                    @error('settings.expiry_alert_days') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <!-- POS Settings -->
                        @if($activeTab === 'pos')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="pos_tax_rate" class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                    <input type="number" id="pos_tax_rate" wire:model="settings.pos_tax_rate" step="0.01" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-sm text-gray-500 mt-1">Tax rate percentage for POS transactions</p>
                                </div>

                                <div>
                                    <label for="pos_allow_discount" class="block text-sm font-medium text-gray-700 mb-2">Allow Discounts</label>
                                    <select id="pos_allow_discount" wire:model="settings.pos_allow_discount"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">Allow cashiers to apply discounts</p>
                                </div>
                            </div>
                        @endif

                        <!-- Receipt Settings -->
                        @if($activeTab === 'receipt')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="receipt_header" class="block text-sm font-medium text-gray-700 mb-2">Receipt Header</label>
                                    <textarea id="receipt_header" wire:model="settings.receipt_header" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">Text to display at the top of receipts</p>
                                </div>

                                <div>
                                    <label for="receipt_footer" class="block text-sm font-medium text-gray-700 mb-2">Receipt Footer</label>
                                    <textarea id="receipt_footer" wire:model="settings.receipt_footer" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">Text to display at the bottom of receipts</p>
                                </div>
                            </div>
                        @endif

                        <!-- Accounting Settings -->
                        @if($activeTab === 'accounting')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-2">Currency Symbol *</label>
                                    <input type="text" id="currency_symbol" wire:model="settings.currency_symbol"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-sm text-gray-500 mt-1">Currency symbol to display (e.g., {{ currency_symbol() }}, $, €)</p>
                                    @error('settings.currency_symbol') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="date_format" class="block text-sm font-medium text-gray-700 mb-2">Date Format *</label>
                                    <select id="date_format" wire:model="settings.date_format"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="d-m-Y">DD-MM-YYYY</option>
                                        <option value="m-d-Y">MM-DD-YYYY</option>
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="Y/m/d">YYYY/MM/DD</option>
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">Date format for displaying dates throughout the system</p>
                                    @error('settings.date_format') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Save Button -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-150 ease-in-out flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
