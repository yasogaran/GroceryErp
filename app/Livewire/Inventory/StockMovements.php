<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class StockMovements extends Component
{
    use WithPagination;

    public $search = '';
    public $productFilter = '';
    public $typeFilter = '';
    public $startDate = '';
    public $endDate = '';

    protected $queryString = ['search', 'productFilter', 'typeFilter', 'startDate', 'endDate'];

    /**
     * Reset pagination when filters are updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingProductFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $movements = StockMovement::query()
            ->with(['product.category', 'performedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->productFilter, function ($query) {
                $query->where('product_id', $this->productFilter);
            })
            ->when($this->typeFilter !== '', function ($query) {
                $query->where('movement_type', $this->typeFilter);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get all products for filter dropdown
        $products = Product::active()
            ->orderBy('name')
            ->get();

        $movementTypes = [
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'damage' => 'Damaged',
            'return' => 'Return',
        ];

        return view('livewire.inventory.stock-movements', [
            'movements' => $movements,
            'products' => $products,
            'movementTypes' => $movementTypes,
        ]);
    }
}
