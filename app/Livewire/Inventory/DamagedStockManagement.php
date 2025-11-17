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

    // Write-off modal
    public $showWriteOffModal = false;
    public $writeOffQuantity = 0;
    public $writeOffReason = '';

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
        $this->showMarkDamagedModal = true;
    }

    public function closeMarkDamagedModal()
    {
        $this->showMarkDamagedModal = false;
        $this->selectedProductId = null;
        $this->selectedProduct = null;
        $this->damageQuantity = 0;
        $this->damageReason = '';
        $this->resetValidation();
    }

    public function markAsDamaged()
    {
        $this->validate();

        $product = Product::find($this->selectedProductId);

        if ($this->damageQuantity > $product->current_stock_quantity) {
            $this->addError('damageQuantity', 'Insufficient stock. Available: ' . $product->current_stock_quantity);
            return;
        }

        try {
            app(InventoryService::class)->markAsDamaged($product, $this->damageQuantity, [
                'reference_type' => 'manual',
                'notes' => $this->damageReason,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock marked as damaged successfully'
            ]);

            $this->closeMarkDamagedModal();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
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

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Damaged stock written-off successfully'
            ]);

            $this->closeWriteOffModal();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Layout('layouts.app')]
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

        $summary = [
            'total_products_with_damage' => Product::where('damaged_stock_quantity', '>', 0)->count(),
            'total_damaged_stock_value' => Product::selectRaw('SUM(damaged_stock_quantity * max_selling_price) as total')->value('total') ?? 0,
            'total_damaged_quantity' => Product::sum('damaged_stock_quantity'),
        ];

        return view('livewire.inventory.damaged-stock-management', [
            'products' => $products,
            'summary' => $summary,
        ]);
    }
}
