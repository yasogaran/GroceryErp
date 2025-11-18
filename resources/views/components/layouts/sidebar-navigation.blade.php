<nav class="px-2 py-4 space-y-1">
    @php
        $menuItems = [
            [
                'name' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'roles' => ['admin', 'manager', 'accountant', 'cashier', 'store_keeper'],
            ],
            [
                'name' => 'POS',
                'route' => 'pos.index',
                'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'name' => 'Categories',
                'route' => 'categories.index',
                'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0a4 4 0 004-4v-4a4 4 0 014-4h.344M15 10l3-3m0 0l-3-3m3 3H9',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Products',
                'route' => 'products.index',
                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Stock Movements',
                'route' => 'stock-movements.index',
                'icon' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Stock Inventory',
                'route' => 'stocks.index',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Stock Adjustments',
                'route' => 'stock-adjustments.index',
                'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Damaged Stock',
                'route' => 'damaged-stock.index',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Barcode Labels',
                'route' => 'barcodes.labels',
                'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Suppliers',
                'route' => 'suppliers.index',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'roles' => ['admin', 'manager'],
            ],
            [
                'name' => 'Goods Receipt (GRN)',
                'route' => 'grn.index',
                'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                'roles' => ['admin', 'manager', 'store_keeper'],
            ],
            [
                'name' => 'Supplier Payments',
                'route' => 'suppliers.payments.index',
                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                'roles' => ['admin', 'manager'],
            ],
            [
                'name' => 'Customers',
                'route' => 'customers.index',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'name' => 'Process Return',
                'route' => 'returns.process',
                'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'name' => 'Return History',
                'route' => 'returns.history',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'name' => 'Offers & Promotions',
                'route' => 'offers.index',
                'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
                'roles' => ['admin', 'manager'],
            ],
            [
                'name' => 'Daily Sales Report',
                'route' => 'reports.daily-sales',
                'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'roles' => ['admin', 'manager'],
            ],
            [
                'name' => 'Stock Report',
                'route' => 'reports.stock',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'roles' => ['admin', 'manager'],
            ],
            [
                'name' => 'Shifts',
                'route' => 'shift.open',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'name' => 'Accounts',
                'route' => 'accounts.index',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'roles' => ['admin', 'accountant'],
            ],
            [
                'name' => 'Journal Entries',
                'route' => 'journal-entries.index',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Trial Balance',
                'route' => 'reports.financial.trial-balance',
                'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Profit & Loss',
                'route' => 'reports.financial.profit-and-loss',
                'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Balance Sheet',
                'route' => 'reports.financial.balance-sheet',
                'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Ledger Report',
                'route' => 'reports.financial.ledger',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Day Book',
                'route' => 'reports.financial.day-book',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'roles' => ['admin', 'accountant', 'manager'],
            ],
            [
                'name' => 'Activity Logs',
                'route' => 'admin.activity-logs',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'roles' => ['admin'],
            ],
            [
                'name' => 'Backups',
                'route' => 'admin.backups.index',
                'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                'roles' => ['admin'],
            ],
            [
                'name' => 'Settings',
                'route' => 'admin.settings',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'roles' => ['admin'],
            ],
            [
                'name' => 'User Management',
                'route' => 'admin.users.index',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'roles' => ['admin'],
            ],
        ];
    @endphp

    @foreach ($menuItems as $item)
        @if (auth()->check() && in_array(auth()->user()->role, $item['roles']))
            @php
                $isActive = false;

                // Check if route exists before using it
                if (Route::has($item['route'])) {
                    $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                }

                $activeClasses = 'bg-gray-800 text-white border-l-4 border-blue-500';
                $inactiveClasses = 'text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent';
            @endphp

            <div class="relative group">
                <a
                    href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                    class="flex items-center text-sm font-medium rounded-lg transition-all duration-150 {{ $isActive ? $activeClasses : $inactiveClasses }}"
                    :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'"
                    :title="sidebarCollapsed ? '{{ $item['name'] }}' : ''"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0 transition-all duration-150"
                        :class="sidebarCollapsed ? 'mr-0' : 'mr-3'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                    </svg>
                    <span
                        x-show="!sidebarCollapsed"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="whitespace-nowrap"
                    >
                        {{ $item['name'] }}
                    </span>
                </a>

                <!-- Tooltip for collapsed state -->
                <div
                    x-show="sidebarCollapsed"
                    class="absolute left-full ml-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-md shadow-lg whitespace-nowrap z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                    style="top: 50%; transform: translateY(-50%);"
                >
                    {{ $item['name'] }}
                </div>
            </div>
        @endif
    @endforeach
</nav>
