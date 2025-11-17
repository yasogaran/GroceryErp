<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Journal Entries</h1>
        <button wire:click="openCreateModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            New Journal Entry
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <input wire:model.live="startDate" type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <input wire:model.live="endDate" type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    @foreach($statusTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="typeFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    @foreach($entryTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Entries Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entry Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Debit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $entry->entry_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->entry_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($entry->description, 50) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $entry->getTypeBadgeClass() }}">
                                {{ ucfirst($entry->entry_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($entry->total_debit, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($entry->total_credit, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $entry->getStatusBadgeClass() }}">
                                {{ ucfirst($entry->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="openViewModal({{ $entry->id }})"
                                class="text-blue-600 hover:text-blue-800 mr-2">View</button>

                            @if($entry->canBeEdited())
                                <button wire:click="openEditModal({{ $entry->id }})"
                                    class="text-green-600 hover:text-green-800 mr-2">Edit</button>
                            @endif

                            @if($entry->canBePosted())
                                <button wire:click="postEntry({{ $entry->id }})"
                                    onclick="return confirm('Post this entry?')"
                                    class="text-purple-600 hover:text-purple-800 mr-2">Post</button>
                            @endif

                            @if($entry->canBeReversed())
                                <button wire:click="openReverseModal({{ $entry->id }})"
                                    class="text-orange-600 hover:text-orange-800 mr-2">Reverse</button>
                            @endif

                            @if($entry->canBeDeleted())
                                <button wire:click="deleteEntry({{ $entry->id }})"
                                    onclick="return confirm('Delete this entry?')"
                                    class="text-red-600 hover:text-red-800">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No journal entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $entries->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold">Create Journal Entry</h3>
                    <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <livewire:journal-entries.create-journal-entry :key="'create-'.now()" />
                </div>
            </div>
        </div>
    @endif

    <!-- Reverse Modal -->
    @if($showReverseModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold">Reverse Journal Entry</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reversal Reason *</label>
                        <textarea wire:model="reversalReason" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        @error('reversalReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="closeModals" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button wire:click="reverseEntry" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reverse Entry</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
