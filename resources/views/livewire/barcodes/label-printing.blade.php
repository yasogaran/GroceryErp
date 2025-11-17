<div class="py-12" x-data="{ showPreview: @entangle('showPreview') }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Barcode Label Printing</h2>
                    <div class="flex space-x-3">
                        @if(count($selectedProducts) > 0)
                            <button wire:click="generatePreview" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Preview {{ count($selectedProducts) }} Label(s)
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Flash Messages -->
                @if (session()->has('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Label Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Label Type</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="labelType" value="product" class="form-radio text-blue-600">
                            <span class="ml-2">Product Labels</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="labelType" value="box" class="form-radio text-blue-600">
                            <span class="ml-2">Box/Package Labels</span>
                        </label>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                        <input wire:model.live="search" type="text" id="search" placeholder="Search by name, SKU, or barcode..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select wire:model.live="categoryFilter" id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Selection Actions -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-600">{{ count($selectedProducts) }} product(s) selected</span>
                    </div>
                    <div class="space-x-2">
                        <button wire:click="selectAll" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                        <button wire:click="deselectAll" class="text-sm text-red-600 hover:text-red-800">Deselect All</button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            wire:click="toggleProduct({{ $product->id }})"
                                            @if(in_array($product->id, $selectedProducts)) checked @endif
                                            class="form-checkbox h-5 w-5 text-blue-600">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        @if($labelType === 'box' && $product->has_packaging && $product->packaging)
                                            <div class="text-xs text-gray-500">{{ $product->packaging->units_per_package }} units per package</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($labelType === 'box' && $product->has_packaging && $product->packaging)
                                            {{ $product->packaging->package_barcode }}
                                        @else
                                            {{ $product->barcode }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category?->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(in_array($product->id, $selectedProducts))
                                            <input type="number"
                                                wire:model.live="quantities.{{ $product->id }}"
                                                min="1" max="1000"
                                                class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreview"
        x-cloak
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        @click.self="$wire.closePreview()">
        <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Label Preview</h3>
                <div class="flex space-x-3">
                    <button @click="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Print Labels
                    </button>
                    <button wire:click="closePreview" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Close
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div class="grid grid-cols-3 gap-4" id="printable-labels">
                    @foreach($previewLabels as $label)
                        @for($i = 0; $i < $label['quantity']; $i++)
                            <div class="border border-gray-300 p-4 rounded bg-white print:break-inside-avoid">
                                <div class="text-center">
                                    <h4 class="font-semibold text-sm mb-2 truncate">{{ $label['product_name'] }}</h4>
                                    <div class="mb-2">
                                        <img src="{{ $label['barcode_image'] }}" alt="Barcode" class="mx-auto" style="max-width: 100%; height: auto;">
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        <p>SKU: {{ $label['sku'] }}</p>
                                        @if(isset($label['barcode']))
                                            <p>Code: {{ $label['barcode'] }}</p>
                                        @endif
                                        @if(isset($label['price']))
                                            <p class="font-semibold text-lg mt-1">{{ format_currency($label['price']) }}</p>
                                        @endif
                                        @if(isset($label['units_per_package']))
                                            <p>{{ $label['units_per_package'] }} units/box</p>
                                        @endif
                                        @if(isset($label['package_price']))
                                            <p class="font-semibold text-lg mt-1">Box: {{ format_currency($label['package_price']) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endfor
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Print Stylesheet -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-labels,
            #printable-labels * {
                visibility: visible;
            }
            #printable-labels {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .print\\:break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</div>
