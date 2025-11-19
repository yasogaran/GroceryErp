<?php

namespace App\Livewire\Inventory;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Services\InventoryService;

class DamagedStockManagement extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $showOnlyDamaged = true;

    // Mark as damaged modal
    public $showMarkDamagedModal = false;
    public $selectedProductId = null;
    public $selectedProduct = null;
    public $damageQuantity = 0;
    public $damageReason = '';
    public $availableBatches = [];
    public $selectedBatchId = null;

    // Write-off modal
    public $showWriteOffModal = false;
    public $writeOffQuantity = 0;
    public $writeOffReason = '';
    public $writeOffAvailableBatches = [];
    public $selectedWriteOffBatchId = null;

    protected $rules = [
        'damageQuantity' => 'required|numeric|min:0.01',
        'damageReason' => 'required|string|max:500',
    ];

    protected $writeOffRules = [
        'writeOffQuantity' => 'required|numeric|min:0.01',
        'writeOffReason' => 'required|string|max:500',
    ];

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingShowOnlyDamaged()
    {
        $this->resetPage();
    }

    public function openMarkDamagedModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->selectedProductId = $productId;
        $this->damageQuantity = 0;
        $this->damageReason = '';
        $this->selectedBatchId = null;

        // Get available batches for this product
        $inventoryService = app(InventoryService::class);
        $this->availableBatches = $inventoryService->getAvailableBatches($this->selectedProduct);

        // Calculate remaining quantity for each batch
        foreach ($this->availableBatches as &$batch) {
            $batch['remaining_quantity'] = $this->calculateBatchRemainingQuantity($productId, $batch['stock_movement_id']);
        }

        // Filter out batches with no remaining quantity
        $this->availableBatches = array_filter($this->availableBatches, function($batch) {
            return $batch['remaining_quantity'] > 0;
        });

        // Reset batch selection (no auto-select)
        $this->selectedBatchId = null;

        $this->showMarkDamagedModal = true;
    }

    public function closeMarkDamagedModal()
    {
        $this->showMarkDamagedModal = false;
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->damageQuantity = 0;
        $this->damageReason = '';
        $this->availableBatches = [];
        $this->selectedBatchId = null;
        $this->resetValidation();
    }

    public function markAsDamaged()
    {
        $this->validate();

        $product = Product::find($this->selectedProductId);

        // Require batch selection when batches are available
        if (count($this->availableBatches) > 0 && !$this->selectedBatchId) {
            $this->addError('selectedBatchId', 'Please select a batch');
            return;
        }

        // Validate quantity against batch if batch is selected
        if ($this->selectedBatchId) {
            $selectedBatch = collect($this->availableBatches)->firstWhere('stock_movement_id', $this->selectedBatchId);
            if ($selectedBatch && $this->damageQuantity > $selectedBatch['remaining_quantity']) {
                $this->addError('damageQuantity', 'Insufficient stock in selected batch. Available: ' . $selectedBatch['remaining_quantity']);
                return;
            }
        } else {
            // No batch selected, validate against total stock
            if ($this->damageQuantity > $product->current_stock_quantity) {
                $this->addError('damageQuantity', 'Insufficient stock. Available: ' . $product->current_stock_quantity);
                return;
            }
        }

        try {
            $details = [
                'notes' => $this->damageReason,
            ];

            // Add batch tracking if batch is selected
            if ($this->selectedBatchId) {
                $details['source_stock_movement_id'] = $this->selectedBatchId;
            }

            app(InventoryService::class)->markAsDamaged($product, $this->damageQuantity, $details);

            $this->dispatch('showToast', type: 'success', message: 'Stock marked as damaged successfully');

            $this->closeMarkDamagedModal();

        } catch (\Exception $e) {
            $this->dispatch('showToast', type: 'error', message: $e->getMessage());
        }
    }

    public function openWriteOffModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->selectedProductId = $productId;
        $this->writeOffQuantity = 0;
        $this->writeOffReason = '';
        $this->showWriteOffModal = true;
    }

    public function closeWriteOffModal()
    {
        $this->showWriteOffModal = false;
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->writeOffQuantity = 0;
        $this->writeOffReason = '';
        $this->resetValidation();
    }

    public function writeOff()
    {
        $this->validate($this->writeOffRules);

        $product = Product::find($this->selectedProductId);

        if ($this->writeOffQuantity > $product->damaged_stock_quantity) {
            $this->addError('writeOffQuantity', 'Insufficient damaged stock. Available: ' . $product->damaged_stock_quantity);
            return;
        }

        try {
            app(InventoryService::class)->writeOffDamaged(
                $product,
                $this->writeOffQuantity,
                $this->writeOffReason
            );

            $this->dispatch('showToast', type: 'success', message: 'Damaged stock written-off successfully');

            $this->closeWriteOffModal();

        } catch (\Exception $e) {
            $this->dispatch('showToast', type: 'error', message: $e->getMessage());
        }
    }

    private function calculateBatchRemainingQuantity($productId, $batchId)
    {
        // Get initial batch quantity
        $batch = \App\Models\StockMovement::find($batchId);
        if (!$batch) {
            return 0;
        }

        $initialQty = $batch->quantity;

        // Get total outgoing movements for this batch using the new source_stock_movement_id field
        $outgoingQty = \App\Models\StockMovement::where('product_id', $productId)
            ->whereIn('movement_type', ['out', 'damage', 'write_off'])
            ->where('source_stock_movement_id', $batchId)
            ->sum('quantity');

        return $initialQty - abs($outgoingQty);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Product::with('category');

        if ($this->showOnlyDamaged) {
            $query->where('damaged_stock_quantity', '>', 0);
        }

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $products = $query->orderBy('damaged_stock_quantity', 'desc')->paginate(20);

        // Calculate summary statistics
        $summaryQuery = Product::query();
        if ($this->showOnlyDamaged) {
            $summaryQuery->where('damaged_stock_quantity', '>', 0);
        }

        // Calculate total damaged stock value using average cost method
        $productsWithDamage = Product::where('damaged_stock_quantity', '>', 0)->with('stockMovements')->get();
        $totalDamagedStockValue = $productsWithDamage->sum(function($product) {
            return $product->getDamagedStockValue();
        });

        $summary = [
            'total_products_with_damage' => $productsWithDamage->count(),
            'total_damaged_stock_value' => $totalDamagedStockValue,
            'total_damaged_quantity' => Product::sum('damaged_stock_quantity'),
        ];

        return view('livewire.inventory.damaged-stock-management', [
            'products' => $products,
            'summary' => $summary,
        ]);
    }
}
