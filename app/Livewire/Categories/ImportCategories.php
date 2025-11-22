<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Services\ExcelImportService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportCategories extends Component
{
    use WithFileUploads, WithToast;

    public $file;
    public $step = 1; // 1: Upload, 2: Validation, 3: Success
    public $validationResults = [];
    public $importSummary = [];

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:2048'
    ];

    public function downloadTemplate()
    {
        $importService = new ExcelImportService();

        $headers = [
            'name',
            'parent_category',
            'description',
            'is_active'
        ];

        $sampleData = [
            ['Beverages', '', 'All types of beverages', 'yes'],
            ['Soft Drinks', 'Beverages', 'Carbonated and non-carbonated drinks', 'yes'],
            ['Dairy', '', 'Milk and dairy products', 'yes'],
            ['Snacks', '', 'Chips, biscuits, and other snacks', 'yes'],
        ];

        $filePath = $importService->generateTemplate($headers, $sampleData, 'categories_template.csv');

        return response()->download($filePath, 'categories_template.csv')->deleteFileAfterSend(true);
    }

    public function processFile()
    {
        $this->validate();

        try {
            $importService = new ExcelImportService();
            $filePath = $this->file->getRealPath();

            // Parse CSV
            $rows = $importService->parseCSV($filePath);

            if (empty($rows)) {
                $this->toastError('No data found in the file');
                return;
            }

            // Validate rows
            $validationRules = [
                'name' => 'required|string|max:255',
                'parent_category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'required|in:yes,no,1,0,true,false'
            ];

            $results = $importService->validateRows($rows, $validationRules);
            $this->validationResults = $importService->formatValidationResults($results);

            // Check for duplicate names in valid rows
            $this->checkDuplicates();

            $this->step = 2;
            $this->toastInfo('File validated. Review the results below.');

        } catch (\Exception $e) {
            $this->toastError('Error processing file: ' . $e->getMessage());
        }
    }

    protected function checkDuplicates()
    {
        $validRows = &$this->validationResults['valid_rows'];
        $invalidRows = &$this->validationResults['invalid_rows'];

        foreach ($validRows as $key => $row) {
            $categoryName = $row['data']['name'];

            // Check if category already exists in database
            $existingCategory = Category::where('name', $categoryName)->first();

            if ($existingCategory) {
                $invalidRows[] = [
                    'row_number' => $row['row_number'],
                    'data' => $row['data'],
                    'errors' => ["Category '{$categoryName}' already exists in the database"]
                ];
                unset($validRows[$key]);
            }
        }

        // Re-index arrays
        $validRows = array_values($validRows);
        $this->validationResults['valid_count'] = count($validRows);
        $this->validationResults['invalid_count'] = count($invalidRows);
    }

    public function import()
    {
        if (empty($this->validationResults['valid_rows'])) {
            $this->toastError('No valid rows to import');
            return;
        }

        try {
            DB::beginTransaction();

            $imported = 0;
            $failed = 0;
            $parentCategories = [];

            // First pass: Import all parent categories (those without parent_category)
            foreach ($this->validationResults['valid_rows'] as $row) {
                if (empty($row['data']['parent_category'])) {
                    $category = $this->createCategory($row['data'], null);
                    if ($category) {
                        $parentCategories[$row['data']['name']] = $category->id;
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
            }

            // Second pass: Import child categories
            foreach ($this->validationResults['valid_rows'] as $row) {
                if (!empty($row['data']['parent_category'])) {
                    $parentId = $parentCategories[$row['data']['parent_category']] ?? null;

                    if ($parentId === null) {
                        // Parent not found in import, check database
                        $parentCategory = Category::where('name', $row['data']['parent_category'])->first();
                        $parentId = $parentCategory ? $parentCategory->id : null;
                    }

                    $category = $this->createCategory($row['data'], $parentId);
                    if ($category) {
                        $imported++;
                    } else {
                        $failed++;
                    }
                }
            }

            DB::commit();

            $this->importSummary = [
                'total' => $this->validationResults['valid_count'],
                'imported' => $imported,
                'failed' => $failed
            ];

            $this->step = 3;
            $this->toastSuccess("Successfully imported {$imported} categories!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Import failed: ' . $e->getMessage());
        }
    }

    protected function createCategory(array $data, ?int $parentId): ?Category
    {
        try {
            return Category::create([
                'name' => $data['name'],
                'parent_id' => $parentId,
                'description' => $data['description'] ?? null,
                'is_active' => in_array(strtolower($data['is_active']), ['yes', '1', 'true']),
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function reset()
    {
        $this->reset(['file', 'step', 'validationResults', 'importSummary']);
    }

    public function render()
    {
        return view('livewire.categories.import-categories')->layout('components.layouts.app');
    }
}
