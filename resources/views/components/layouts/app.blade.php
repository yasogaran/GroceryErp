<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Grocery ERP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', userDropdownOpen: false }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 left-0 z-50 bg-gray-900 transform transition-all duration-300 ease-in-out flex flex-col"
                :class="{
                    'w-64': !sidebarCollapsed,
                    'w-20': sidebarCollapsed,
                    'translate-x-0': sidebarOpen || window.innerWidth >= 1024,
                    '-translate-x-full lg:translate-x-0': !sidebarOpen && window.innerWidth < 1024
                }"
                @click.away="sidebarOpen = false"
            >
                <!-- Logo and Toggle Button -->
                <div class="flex items-center justify-between h-16 px-4 bg-gray-800 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="font-bold text-white transition-all duration-300" :class="sidebarCollapsed ? 'text-lg' : 'text-xl'">
                        <span x-show="!sidebarCollapsed" x-transition>Grocery ERP</span>
                        <span x-show="sidebarCollapsed" x-transition class="text-center block">GE</span>
                    </a>
                    <!-- Desktop Collapse Button -->
                    <button
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:block text-gray-400 hover:text-white"
                        :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                    <!-- Mobile Close Button -->
                    <button
                        @click="sidebarOpen = false"
                        class="text-gray-400 hover:text-white lg:hidden"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- User Profile Section -->
                @auth
                <div class="px-4 py-4 border-b border-gray-800 flex-shrink-0">
                    <div class="flex items-center space-x-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-medium text-lg">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                        <div x-show="!sidebarCollapsed" x-transition class="flex-1 min-w-0">
                            <div class="font-medium text-white text-sm truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- Navigation Container with Scroll -->
                <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-gray-800">
                    <!-- Navigation -->
                    @include('components.layouts.sidebar-navigation')
                </div>

                <!-- Sign Out Button at Bottom -->
                @auth
                <div class="px-4 py-4 border-t border-gray-800 flex-shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center text-sm font-medium text-gray-300 hover:bg-red-600 hover:text-white rounded-lg transition-colors duration-150 border-l-4 border-transparent hover:border-red-500"
                            :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'"
                            :title="sidebarCollapsed ? 'Sign Out' : ''"
                        >
                            <svg class="w-5 h-5 flex-shrink-0 transition-all duration-150" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Sign Out</span>
                        </button>
                    </form>
                </div>
                @endauth
            </aside>

            <!-- Main Content -->
            <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" class="transition-all duration-300">
                <!-- Mobile menu button (floating) -->
                <button
                    @click="sidebarOpen = true"
                    class="lg:hidden fixed top-4 left-4 z-30 bg-gray-900 text-white p-2 rounded-lg shadow-lg"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Page Content -->
                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Overlay for mobile sidebar -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            style="display: none;"
        ></div>

        <!-- Toast Notification Component -->
        @livewire('toast')

        <!-- Alpine.js -->
    </body>
</html>
