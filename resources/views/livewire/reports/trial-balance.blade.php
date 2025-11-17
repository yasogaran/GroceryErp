<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Trial Balance</h1>

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
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($reportData['accounts'] as $balance)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $balance['account']->account_code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $balance['account']->account_name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $balance['account']->getTypeBadgeClass() }}">
                                    {{ ucfirst($balance['account']->account_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">₹{{ number_format($balance['debit'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">₹{{ number_format($balance['credit'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="px-6 py-4 text-sm text-right">TOTAL</td>
                        <td class="px-6 py-4 text-sm text-right">₹{{ number_format($reportData['total_debit'], 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right">₹{{ number_format($reportData['total_credit'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="p-4 {{ $reportData['is_balanced'] ? 'bg-green-50' : 'bg-red-50' }}">
                @if($reportData['is_balanced'])
                    <p class="text-green-800 font-semibold">✓ Trial Balance is Balanced</p>
                @else
                    <p class="text-red-800 font-semibold">✗ Trial Balance is NOT Balanced (Difference: ₹{{ number_format(abs($reportData['difference']), 2) }})</p>
                @endif
            </div>
        </div>
    @endif
</div>
