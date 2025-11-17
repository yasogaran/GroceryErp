<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Day Book</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input wire:model="date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
            @forelse($reportData['entries'] as $entry)
                <div class="border-b p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $entry->entry_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $entry->description }}</p>
                            <span class="inline-block px-2 py-1 text-xs rounded-full mt-1 {{ $entry->getTypeBadgeClass() }}">
                                {{ ucfirst($entry->entry_type) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Created by</p>
                            <p class="font-medium text-gray-900">{{ $entry->creator->name }}</p>
                        </div>
                    </div>

                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-t">
                                <th class="py-2 text-left text-gray-600">Account</th>
                                <th class="py-2 text-right text-gray-600">Debit</th>
                                <th class="py-2 text-right text-gray-600">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entry->lines as $line)
                                <tr>
                                    <td class="py-1 text-gray-700">{{ $line->account->account_code }} - {{ $line->account->account_name }}</td>
                                    <td class="py-1 text-right text-gray-900">{{ $line->debit > 0 ? '₹' . number_format($line->debit, 2) : '-' }}</td>
                                    <td class="py-1 text-right text-gray-900">{{ $line->credit > 0 ? '₹' . number_format($line->credit, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    No entries found for this date.
                </div>
            @endforelse

            @if(count($reportData['entries']) > 0)
                <div class="p-6 bg-gray-50">
                    <div class="flex justify-end space-x-8">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Debit</p>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($reportData['total_debit'], 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Credit</p>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($reportData['total_credit'], 2) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
