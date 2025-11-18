@if($dashboardComponent)
    @livewire($dashboardComponent)
@else
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

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Role-specific dashboard not found</h3>
                        <p class="mt-1 text-sm text-blue-700">
                            Your role doesn't have a specific dashboard configured yet. Please contact your administrator.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
