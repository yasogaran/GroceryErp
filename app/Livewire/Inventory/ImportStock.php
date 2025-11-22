<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\ExcelImportService;
use App\Services\InventoryService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportStock extends Component
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
            'product_sku',
            'product_name',
            'quantity',
            'unit_cost',
            'supplier_name',
            'batch_number',
            'manufacturing_date',
            'expiry_date',
            'notes'
        ];

        $sampleData = [
            ['PROD001', 'Coca Cola 500ml', '100', '35.00', 'ABC Distributors', 'BATCH001', '2024-01-15', '2025-01-15', 'Initial stock'],
            ['PROD002', 'Lays Chips 100g', '200', '28.00', 'XYZ Suppliers', 'BATCH002', '2024-01-10', '2024-12-31', 'Bulk order'],
            ['', 'Fresh Milk 1L', '50', '45.00', 'Dairy Farm', '', '', '2024-02-28', 'Perishable item'],
        ];

        $filePath = $importService->generateTemplate($headers, $sampleData, 'stock_template.csv');

        return response()->download($filePath, 'stock_template.csv')->deleteFileAfterSend(true);
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
                'product_sku' => 'nullable|string|max:50',
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0.01',
                'unit_cost' => 'required|numeric|min:0',
                'supplier_name' => 'nullable|string|max:255',
                'batch_number' => 'nullable|string|max:100',
                'manufacturing_date' => 'nullable|date',
                'expiry_date' => 'nullable|date',
                'notes' => 'nullable|string'
            ];

            $results = $importService->validateRows($rows, $validationRules);
            $this->validationResults = $importService->formatValidationResults($results);

            // Additional validations
            $this->checkProductsExist();

            $this->step = 2;
            $this->toastInfo('File validated. Review the results below.');

        } catch (\Exception $e) {
            $this->toastError('Error processing file: ' . $e->getMessage());
        }
    }

    protected function checkProductsExist()
    {
        $validRows = &$this->validationResults['valid_rows'];
        $invalidRows = &$this->validationResults['invalid_rows'];

        foreach ($validRows as $key => $row) {
            $errors = [];
            $data = $row['data'];

            // Find product by SKU or name
            $product = null;
            if (!empty($data['product_sku'])) {
                $product = Product::where('sku', $data['product_sku'])->first();
            }

            if (!$product) {
                $product = Product::where('name', $data['product_name'])->first();
            }

            if (!$product) {
                $errors[] = "Product '{$data['product_name']}' " .
                           (!empty($data['product_sku']) ? "(SKU: {$data['product_sku']}) " : "") .
                           "not found in the database";
            }

            // Validate dates
            if (!empty($data['manufacturing_date']) && !empty($data['expiry_date'])) {
                if (strtotime($data['manufacturing_date']) > strtotime($data['expiry_date'])) {
                    $errors[] = "Manufacturing date cannot be after expiry date";
                }
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

            $inventoryService = app(InventoryService::class);
            $imported = 0;
            $failed = 0;

            foreach ($this->validationResults['valid_rows'] as $row) {
                $success = $this->addStock($row['data'], $inventoryService);
                if ($success) {
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
            $this->toastSuccess("Successfully imported stock for {$imported} products!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Import failed: ' . $e->getMessage());
        }
    }

    protected function addStock(array $data, InventoryService $inventoryService): bool
    {
        try {
            // Find product
            $product = null;
            if (!empty($data['product_sku'])) {
                $product = Product::where('sku', $data['product_sku'])->first();
            }

            if (!$product) {
                $product = Product::where('name', $data['product_name'])->first();
            }

            if (!$product) {
                return false;
            }

            // Find or create supplier if provided
            $supplierId = null;
            $supplierName = null;

            if (!empty($data['supplier_name'])) {
                $supplier = Supplier::where('name', $data['supplier_name'])->first();
                if ($supplier) {
                    $supplierId = $supplier->id;
                    $supplierName = $supplier->name;
                } else {
                    $supplierName = $data['supplier_name'];
                }
            }

            // Prepare stock details
            $details = [
                'unit_cost' => $data['unit_cost'],
                'min_selling_price' => $product->min_selling_price,
                'max_selling_price' => $product->max_selling_price,
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
                'batch_number' => $data['batch_number'] ?? null,
                'manufacturing_date' => !empty($data['manufacturing_date']) ? $data['manufacturing_date'] : null,
                'expiry_date' => !empty($data['expiry_date']) ? $data['expiry_date'] : null,
                'notes' => $data['notes'] ?? 'Imported stock',
            ];

            // Add stock using InventoryService
            $inventoryService->addStock($product, $data['quantity'], $details);

            return true;
        } catch (\Exception $e) {
            \Log::error('Stock import error: ' . $e->getMessage());
            return false;
        }
    }

    public function reset()
    {
        $this->reset(['file', 'step', 'validationResults', 'importSummary']);
    }

    public function render()
    {
        return view('livewire.inventory.import-stock')->layout('components.layouts.app');
    }
}
