<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Import Categories</h2>
        <p class="text-gray-600 mt-1">Upload a CSV file to import multiple categories at once</p>
    </div>

    <!-- Step 1: Upload File -->
    @if($step === 1)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Step 1: Upload File</h3>
                <p class="text-gray-600 text-sm">Download the template, fill in your data, and upload the file</p>
            </div>

            <!-- Download Template -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-900">Download Template</h4>
                        <p class="text-sm text-blue-800 mb-3">Start by downloading the template file with the correct format and sample data</p>
                        <button wire:click="downloadTemplate" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Download Template
                        </button>
                    </div>
                </div>
            </div>

            <!-- Template Instructions -->
            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2">Template Format:</h4>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li><strong>name</strong> (required): Category name</li>
                    <li><strong>parent_category</strong> (optional): Parent category name for sub-categories</li>
                    <li><strong>description</strong> (optional): Category description</li>
                    <li><strong>is_active</strong> (required): yes/no or 1/0 or true/false</li>
                </ul>
            </div>

            <!-- File Upload -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload CSV File
                </label>
                <input type="file" wire:model="file" accept=".csv,.txt" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                    cursor-pointer border border-gray-300 rounded-lg">
                @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div wire:loading wire:target="file" class="text-sm text-blue-600 mt-2">
                    Uploading file...
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button wire:click="processFile" wire:loading.attr="disabled" type="button"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="processFile">Process File</span>
                    <span wire:loading wire:target="processFile">Processing...</span>
                </button>
                <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </a>
            </div>
        </div>
    @endif

    <!-- Step 2: Validation Results -->
    @if($step === 2)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Step 2: Validation Results</h3>
                <p class="text-gray-600 text-sm">Review the validation results before importing</p>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-600">{{ $validationResults['total_rows'] }}</div>
                    <div class="text-sm text-blue-800">Total Rows</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-600">{{ $validationResults['valid_count'] }}</div>
                    <div class="text-sm text-green-800">Valid Rows</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-2xl font-bold text-red-600">{{ $validationResults['invalid_count'] }}</div>
                    <div class="text-sm text-red-800">Invalid Rows</div>
                </div>
            </div>

            <!-- Valid Rows -->
            @if(count($validationResults['valid_rows']) > 0)
                <div class="mb-6">
                    <h4 class="font-semibold text-green-700 mb-3">Valid Rows ({{ count($validationResults['valid_rows']) }})</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($validationResults['valid_rows'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['row_number'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['data']['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row['data']['parent_category'] ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row['data']['description'] ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full {{ in_array(strtolower($row['data']['is_active']), ['yes', '1', 'true']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ in_array(strtolower($row['data']['is_active']), ['yes', '1', 'true']) ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Invalid Rows -->
            @if(count($validationResults['invalid_rows']) > 0)
                <div class="mb-6">
                    <h4 class="font-semibold text-red-700 mb-3">Invalid Rows ({{ count($validationResults['invalid_rows']) }})</h4>
                    <div class="space-y-3">
                        @foreach($validationResults['invalid_rows'] as $row)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="font-semibold text-red-900 mb-2">Row #{{ $row['row_number'] }}</div>
                                <div class="text-sm text-red-800 mb-2">
                                    <strong>Data:</strong> {{ implode(', ', array_filter($row['data'])) }}
                                </div>
                                <div class="text-sm text-red-700">
                                    <strong>Errors:</strong>
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach($row['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex space-x-3">
                <button wire:click="import" wire:loading.attr="disabled"
                    @if($validationResults['valid_count'] === 0) disabled @endif
                    type="button" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">Import {{ $validationResults['valid_count'] }} Categories</span>
                    <span wire:loading wire:target="import">Importing...</span>
                </button>
                <button wire:click="reset" type="button" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Start Over
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Success -->
    @if($step === 3)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Import Completed!</h3>
                <p class="text-gray-600 mb-6">Your categories have been imported successfully</p>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 max-w-md mx-auto mb-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-gray-600">Total Rows:</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $importSummary['total'] }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Imported:</div>
                            <div class="text-2xl font-bold text-green-600">{{ $importSummary['imported'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center space-x-3">
                    <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        View Categories
                    </a>
                    <button wire:click="reset" type="button" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Import More
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
