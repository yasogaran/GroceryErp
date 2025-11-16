<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manage Offers</h1>
        <a href="{{ route('offers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
            Create New Offer
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Search offers..."
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select wire:model.live="typeFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">All Types</option>
                    <option value="buy_x_get_y">Buy X Get Y</option>
                    <option value="quantity_discount">Quantity Discount</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Offers List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($offers as $offer)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $offer->name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($offer->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $offer->offer_type === 'buy_x_get_y' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $offer->offer_type === 'buy_x_get_y' ? 'Buy X Get Y' : 'Quantity Discount' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($offer->offer_type === 'buy_x_get_y')
                                Buy {{ $offer->buy_quantity }} Get {{ $offer->get_quantity }} Free
                            @else
                                Min {{ $offer->min_quantity }} items:
                                {{ $offer->discount_type === 'percentage' ? $offer->discount_value . '%' : 'Rs. ' . $offer->discount_value }} off
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ $offer->start_date->format('M d, Y') }} -
                            {{ $offer->end_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ $offer->priority }}
                        </td>
                        <td class="px-6 py-4">
                            <button
                                wire:click="toggleActive({{ $offer->id }})"
                                class="px-3 py-1 text-xs rounded {{ $offer->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $offer->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('offers.edit', $offer->id) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <button
                                    wire:click="deleteOffer({{ $offer->id }})"
                                    wire:confirm="Are you sure you want to delete this offer?"
                                    class="text-red-600 hover:text-red-800">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No offers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $offers->links() }}
        </div>
    </div>
</div>
