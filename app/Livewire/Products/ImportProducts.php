<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Services\ExcelImportService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportProducts extends Component
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
            'sku',
            'barcode',
            'name',
            'description',
            'category',
            'brand',
            'base_unit',
            'min_selling_price',
            'max_selling_price',
            'reorder_level',
            'is_active'
        ];

        $sampleData = [
            ['PROD001', '1234567890123', 'Coca Cola 500ml', 'Carbonated soft drink', 'Beverages', 'Coca Cola', 'piece', '45.00', '50.00', '20', 'yes'],
            ['PROD002', '1234567890124', 'Lays Chips 100g', 'Classic salted potato chips', 'Snacks', 'Lays', 'piece', '40.00', '45.00', '30', 'yes'],
            ['PROD003', '', 'Fresh Milk 1L', 'Full cream milk', 'Dairy', 'Amul', 'liter', '55.00', '60.00', '15', 'yes'],
        ];

        $filePath = $importService->generateTemplate($headers, $sampleData, 'products_template.csv');

        return response()->download($filePath, 'products_template.csv')->deleteFileAfterSend(true);
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
                'sku' => 'nullable|string|max:50',
                'barcode' => 'nullable|string|max:50',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string|max:255',
                'brand' => 'nullable|string|max:255',
                'base_unit' => 'required|in:piece,kg,gram,liter,ml,meter,cm,box,pack',
                'min_selling_price' => 'required|numeric|min:0',
                'max_selling_price' => 'required|numeric|min:0',
                'reorder_level' => 'nullable|numeric|min:0',
                'is_active' => 'required|in:yes,no,1,0,true,false'
            ];

            $results = $importService->validateRows($rows, $validationRules);
            $this->validationResults = $importService->formatValidationResults($results);

            // Additional validations
            $this->checkDuplicatesAndCategories();

            $this->step = 2;
            $this->toastInfo('File validated. Review the results below.');

        } catch (\Exception $e) {
            $this->toastError('Error processing file: ' . $e->getMessage());
        }
    }

    protected function checkDuplicatesAndCategories()
    {
        $validRows = &$this->validationResults['valid_rows'];
        $invalidRows = &$this->validationResults['invalid_rows'];

        foreach ($validRows as $key => $row) {
            $errors = [];
            $data = $row['data'];

            // Check if category exists
            $category = Category::where('name', $data['category'])->first();
            if (!$category) {
                $errors[] = "Category '{$data['category']}' does not exist";
            }

            // Check SKU duplicate if provided
            if (!empty($data['sku'])) {
                $existingProduct = Product::where('sku', $data['sku'])->first();
                if ($existingProduct) {
                    $errors[] = "SKU '{$data['sku']}' already exists";
                }
            }

            // Check barcode duplicate if provided
            if (!empty($data['barcode'])) {
                $existingProduct = Product::where('barcode', $data['barcode'])->first();
                if ($existingProduct) {
                    $errors[] = "Barcode '{$data['barcode']}' already exists";
                }
            }

            // Check product name duplicate
            $existingProduct = Product::where('name', $data['name'])->first();
            if ($existingProduct) {
                $errors[] = "Product name '{$data['name']}' already exists";
            }

            // Check price range
            if ($data['min_selling_price'] > $data['max_selling_price']) {
                $errors[] = "Min selling price cannot be greater than max selling price";
            }

            if (!empty($errors)) {
                $invalidRows[] = [
                    'row_number' => $row['row_number'],
                    'data' => $data,
                    'errors' => $errors
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

            foreach ($this->validationResults['valid_rows'] as $row) {
                $product = $this->createProduct($row['data']);
                if ($product) {
                    $imported++;
                } else {
                    $failed++;
                }
            }

            DB::commit();

            $this->importSummary = [
                'total' => $this->validationResults['valid_count'],
                'imported' => $imported,
                'failed' => $failed
            ];

            $this->step = 3;
            $this->toastSuccess("Successfully imported {$imported} products!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Import failed: ' . $e->getMessage());
        }
    }

    protected function createProduct(array $data): ?Product
    {
        try {
            $category = Category::where('name', $data['category'])->first();

            if (!$category) {
                return null;
            }

            return Product::create([
                'sku' => !empty($data['sku']) ? $data['sku'] : null,
                'barcode' => !empty($data['barcode']) ? $data['barcode'] : null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $category->id,
                'brand' => $data['brand'] ?? null,
                'base_unit' => $data['base_unit'],
                'min_selling_price' => $data['min_selling_price'],
                'max_selling_price' => $data['max_selling_price'],
                'reorder_level' => $data['reorder_level'] ?? 0,
                'current_stock_quantity' => 0,
                'damaged_stock_quantity' => 0,
                'is_active' => in_array(strtolower($data['is_active']), ['yes', '1', 'true']),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
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
        return view('livewire.products.import-products')->layout('components.layouts.app');
    }
}
