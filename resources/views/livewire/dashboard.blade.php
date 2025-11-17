<div>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-gray-600">
                You are logged in as <span class="font-semibold text-blue-600">{{ ucfirst(auth()->user()->role) }}</span>
            </p>
        </div>

        {{-- Admin/Manager Dashboard --}}
        @if(in_array($role, ['admin', 'manager']))
            @include('livewire.dashboard.admin-dashboard', ['data' => $data])
        @endif

        {{-- Cashier Dashboard --}}
        @if($role === 'cashier')
            @include('livewire.dashboard.cashier-dashboard', ['data' => $data])
        @endif

        {{-- Store Keeper Dashboard --}}
        @if($role === 'store_keeper')
            @include('livewire.dashboard.storekeeper-dashboard', ['data' => $data])
        @endif

        {{-- Accountant Dashboard --}}
        @if($role === 'accountant')
            @include('livewire.dashboard.accountant-dashboard', ['data' => $data])
        @endif

        {{-- Default Dashboard for other roles --}}
        @if(!in_array($role, ['admin', 'manager', 'cashier', 'store_keeper', 'accountant']))
            @include('livewire.dashboard.default-dashboard', ['data' => $data])
        @endif
    </div>
</div>
