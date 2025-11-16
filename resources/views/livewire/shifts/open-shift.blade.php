<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Open Shift
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Welcome, {{ auth()->user()->name }}
            </p>
            <p class="mt-1 text-center text-xs text-gray-500">
                {{ now()->format('l, F j, Y - g:i A') }}
            </p>
        </div>

        <form wire:submit="openShift" class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm bg-white p-8">
                <div>
                    <label for="openingCash" class="block text-sm font-medium text-gray-700 mb-2">
                        Opening Cash Amount (Rs.)
                    </label>
                    <input
                        wire:model="openingCash"
                        type="number"
                        step="0.01"
                        min="0"
                        id="openingCash"
                        class="appearance-none relative block w-full px-3 py-4 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 text-2xl text-center font-semibold"
                        placeholder="0.00"
                        autofocus
                    >
                    @error('openingCash')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Count the cash in your drawer and enter the total amount. This will be your opening cash balance for this shift.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </span>
                    Start Shift
                </button>
            </div>
        </form>
    </div>
</div>
