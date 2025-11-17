<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GroceryERP - Professional Grocery Store Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gradient-to-br from-blue-50 via-white to-green-50">
        <div class="min-h-screen">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <!-- Logo -->
                        <div class="flex items-center space-x-3">
                            <div class="bg-gradient-to-br from-blue-600 to-green-600 rounded-lg p-2">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">GroceryERP</h1>
                                <p class="text-xs text-gray-500">Enterprise Resource Planning</p>
                            </div>
                        </div>

                        <!-- Navigation -->
                        @if (Route::has('login'))
                            <nav class="flex items-center space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        Sign In
                                    </a>
                                @endauth
                            </nav>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Hero Section -->
            <main>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <!-- Hero Content -->
                    <div class="text-center mb-16">
                        <h2 class="text-5xl font-bold text-gray-900 mb-4">
                            Streamline Your Grocery
                            <span class="bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">Business Operations</span>
                        </h2>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
                            Complete enterprise solution for managing inventory, sales, suppliers, and customer relationships. Built for modern grocery stores and retail businesses.
                        </p>
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-green-600 text-white text-lg font-semibold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Get Started Now
                            </a>
                        @endguest
                    </div>

                    <!-- Features Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                        <!-- Feature 1: POS System -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Point of Sale</h3>
                            <p class="text-gray-600">Fast and intuitive POS system with batch selection, loyalty programs, offers, and multi-payment support.</p>
                        </div>

                        <!-- Feature 2: Inventory Management -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Inventory Control</h3>
                            <p class="text-gray-600">Advanced batch tracking with FIFO/manual selection, expiry monitoring, and real-time stock status.</p>
                        </div>

                        <!-- Feature 3: Supplier Management -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Supplier Relations</h3>
                            <p class="text-gray-600">Comprehensive supplier management with GRN processing, ledger tracking, and payment history.</p>
                        </div>

                        <!-- Feature 4: Sales & Reports -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Sales Analytics</h3>
                            <p class="text-gray-600">Detailed sales reports, profit analysis, daily summaries, and business intelligence dashboards.</p>
                        </div>

                        <!-- Feature 5: Customer Loyalty -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Loyalty Program</h3>
                            <p class="text-gray-600">Built-in customer loyalty system with points tracking, rewards, and customer purchase history.</p>
                        </div>

                        <!-- Feature 6: Multi-User Access -->
                        <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
                            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg w-14 h-14 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Role-Based Access</h3>
                            <p class="text-gray-600">Secure multi-user system with role-based permissions for admin, manager, cashier, and store keeper.</p>
                        </div>
                    </div>

                    <!-- Key Benefits -->
                    <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl shadow-2xl p-12 text-white">
                        <div class="text-center mb-12">
                            <h3 class="text-3xl font-bold mb-4">Why Choose GroceryERP?</h3>
                            <p class="text-xl text-blue-100">The complete solution for modern grocery store management</p>
                        </div>

                        <div class="grid md:grid-cols-4 gap-8">
                            <div class="text-center">
                                <div class="text-4xl font-bold mb-2">100%</div>
                                <div class="text-blue-100">Batch Traceability</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold mb-2">Real-Time</div>
                                <div class="text-blue-100">Stock Updates</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold mb-2">Multi</div>
                                <div class="text-blue-100">Payment Support</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold mb-2">24/7</div>
                                <div class="text-blue-100">Access Available</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-gray-900 text-gray-400 py-8 mt-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="flex items-center justify-center space-x-2 mb-4">
                            <div class="bg-gradient-to-br from-blue-600 to-green-600 rounded-lg p-1.5">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-white font-semibold text-lg">GroceryERP</span>
                        </div>
                        <p class="text-sm">
                            Professional Grocery Store Management System
                        </p>
                        <p class="text-xs mt-2">
                            &copy; {{ date('Y') }} GroceryERP. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
