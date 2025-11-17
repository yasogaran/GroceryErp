<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Balance Sheet</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">As of Date</label>
                <input wire:model="asOfDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
            <!-- Assets Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="bg-green-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-semibold text-green-800">Assets</h2>
                </div>
                <div class="p-6">
                    @foreach($reportData['asset_accounts'] as $account)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-700">{{ $account['account']->account_name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ format_currency($account['debit'] - $account['credit']) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-3 mt-3 border-t-2 border-green-300 font-bold">
                        <span>Total Assets</span>
                        <span class="text-green-600">{{ format_currency($reportData['total_assets']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Liabilities & Equity Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="bg-red-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-semibold text-red-800">Liabilities & Equity</h2>
                </div>
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-2">Liabilities:</h3>
                    @foreach($reportData['liability_accounts'] as $account)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-700 pl-4">{{ $account['account']->account_name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ format_currency($account['credit'] - $account['debit']) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-2 mt-2 border-t">
                        <span class="text-sm font-semibold">Total Liabilities</span>
                        <span class="text-sm font-semibold">{{ format_currency($reportData['total_liabilities']) }}</span>
                    </div>

                    <h3 class="text-sm font-bold text-gray-700 mt-4 mb-2">Equity:</h3>
                    @foreach($reportData['equity_accounts'] as $account)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-700 pl-4">{{ $account['account']->account_name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ format_currency($account['credit'] - $account['debit']) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-700 pl-4">Current Year P/L</span>
                        <span class="text-sm font-medium text-gray-900">{{ format_currency($reportData['current_year_pl']) }}</span>
                    </div>
                    <div class="flex justify-between py-2 mt-2 border-t">
                        <span class="text-sm font-semibold">Total Equity</span>
                        <span class="text-sm font-semibold">{{ format_currency($reportData['total_equity']) }}</span>
                    </div>

                    <div class="flex justify-between py-3 mt-3 border-t-2 border-red-300 font-bold">
                        <span>Total Liabilities & Equity</span>
                        <span class="text-red-600">{{ format_currency($reportData['total_liabilities_and_equity']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Check -->
        <div class="mt-6 p-4 {{ $reportData['is_balanced'] ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
            @if($reportData['is_balanced'])
                <p class="text-green-800 font-semibold">✓ Balance Sheet is Balanced</p>
            @else
                <p class="text-red-800 font-semibold">✗ Balance Sheet is NOT Balanced (Difference: {{ format_currency(abs($reportData['difference'])) }})</p>
            @endif
        </div>
    @endif
</div>
