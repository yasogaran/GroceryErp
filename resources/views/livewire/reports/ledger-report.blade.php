<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Account Ledger Report</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account</label>
                <select wire:model="accountId" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Select Account</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                    @endforeach
                </select>
            </div>
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
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">{{ $reportData['account']->account_name }}</h2>
                <p class="text-sm text-gray-500">{{ $reportData['account']->account_code }} - {{ ucfirst($reportData['account']->account_type) }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entry #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($reportData['transactions'] as $transaction)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction['date']->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction['entry_number'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $transaction['description'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $transaction['debit'] > 0 ? '₹' . number_format($transaction['debit'], 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $transaction['credit'] > 0 ? '₹' . number_format($transaction['credit'], 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">₹{{ number_format($transaction['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No transactions found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="flex justify-end">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Closing Balance</p>
                        <p class="text-lg font-bold text-gray-900">₹{{ number_format($reportData['closing_balance'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
