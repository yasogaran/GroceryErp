<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Profit & Loss Statement</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input wire:model="startDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input wire:model="endDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-end">
                <button wire:click="generateReport" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    @if($reportData)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Income Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="bg-blue-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-semibold text-blue-800">Income</h2>
                </div>
                <div class="p-6">
                    @foreach($reportData['income_accounts'] as $account)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-700">{{ $account['account']->account_name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ format_currency($account['credit'] - $account['debit']) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-3 mt-3 border-t-2 border-blue-300 font-bold">
                        <span>Total Income</span>
                        <span class="text-blue-600">{{ format_currency($reportData['total_income']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="bg-orange-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-semibold text-orange-800">Expenses</h2>
                </div>
                <div class="p-6">
                    @foreach($reportData['expense_accounts'] as $account)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-700">{{ $account['account']->account_name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ format_currency($account['debit'] - $account['credit']) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-3 mt-3 border-t-2 border-orange-300 font-bold">
                        <span>Total Expenses</span>
                        <span class="text-orange-600">{{ format_currency($reportData['total_expenses']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit/Loss -->
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Net {{ $reportData['is_profit'] ? 'Profit' : 'Loss' }}</h2>
                <span class="text-2xl font-bold {{ $reportData['is_profit'] ? 'text-green-600' : 'text-red-600' }}">
                    {{ format_currency(abs($reportData['net_profit'])) }}
                </span>
            </div>
        </div>
    @endif
</div>
