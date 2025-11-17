<nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
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
                'name' => 'Activity Logs',
                'route' => 'admin.activity-logs',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
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

            <a
                href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-150 {{ $isActive ? $activeClasses : $inactiveClasses }}"
            >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                </svg>
                {{ $item['name'] }}
            </a>
        @endif
    @endforeach
</nav>
