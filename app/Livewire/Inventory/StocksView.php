<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StocksView extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $minPrice = '';
    public $maxPrice = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
    }

    public function getStockStatus($batch)
    {
        $today = Carbon::today();

        // Check if expired
        if ($batch->expiry_date && Carbon::parse($batch->expiry_date)->lt($today)) {
            return [
                'status' => 'expired',
                'label' => 'Expired',
                'class' => 'bg-red-100 text-red-800'
            ];
        }

        // Check if expiring soon (within 30 days)
        if ($batch->expiry_date && Carbon::parse($batch->expiry_date)->lte($today->copy()->addDays(30))) {
            return [
                'status' => 'expiring_soon',
                'label' => 'Expiring Soon',
                'class' => 'bg-yellow-100 text-yellow-800'
            ];
        }

        // Check if low stock (below reorder level)
        if ($batch->product && $batch->remaining_quantity <= $batch->product->reorder_level) {
            return [
                'status' => 'low_stock',
                'label' => 'Low Stock',
                'class' => 'bg-orange-100 text-orange-800'
            ];
        }

        // Active stock
        return [
            'status' => 'active',
            'label' => 'Active',
            'class' => 'bg-green-100 text-green-800'
        ];
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        // Build the query for stock batches
        $query = StockMovement::query()
            ->where('movement_type', 'in')
            ->whereNotNull('unit_cost')
            ->with(['product.category']);

        // Calculate remaining quantity for each batch
        $query->select('stock_movements.*')
            ->selectRaw('(
                SELECT COALESCE(SUM(quantity), 0)
                FROM stock_movements sm
                WHERE sm.product_id = stock_movements.product_id
                AND sm.created_at >= stock_movements.created_at
                AND sm.id != stock_movements.id
            ) as total_movements')
            ->selectRaw('(stock_movements.quantity - (
                SELECT COALESCE(SUM(ABS(quantity)), 0)
                FROM stock_movements sm
                WHERE sm.product_id = stock_movements.product_id
                AND sm.movement_type IN ("out", "damage", "write_off")
                AND sm.reference_type = "App\\\\Models\\\\StockMovement"
                AND sm.reference_id = stock_movements.id
            )) as remaining_quantity');

        // Apply filters
        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', $this->categoryFilter);
            });
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->minPrice) {
            $query->where('min_selling_price', '>=', $this->minPrice);
        }

        if ($this->maxPrice) {
            $query->where('max_selling_price', '<=', $this->maxPrice);
        }

        // Get all results first for status filtering
        $allBatches = $query->get()->filter(function ($batch) {
            return $batch->remaining_quantity > 0;
        });

        // Apply status filter
        if ($this->statusFilter) {
            $allBatches = $allBatches->filter(function ($batch) {
                $status = $this->getStockStatus($batch);
                return $status['status'] === $this->statusFilter;
            });
        }

        // Paginate manually
        $currentPage = $this->getPage();
        $batches = new \Illuminate\Pagination\LengthAwarePaginator(
            $allBatches->forPage($currentPage, $this->perPage),
            $allBatches->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Calculate summary statistics
        $totalBatches = $allBatches->count();
        $totalValue = $allBatches->sum(function ($batch) {
            return $batch->remaining_quantity * $batch->unit_cost;
        });
        $activeBatches = $allBatches->filter(function ($batch) {
            $status = $this->getStockStatus($batch);
            return $status['status'] === 'active';
        })->count();
        $expiringBatches = $allBatches->filter(function ($batch) {
            $status = $this->getStockStatus($batch);
            return $status['status'] === 'expiring_soon';
        })->count();
        $expiredBatches = $allBatches->filter(function ($batch) {
            $status = $this->getStockStatus($batch);
            return $status['status'] === 'expired';
        })->count();
        $lowStockBatches = $allBatches->filter(function ($batch) {
            $status = $this->getStockStatus($batch);
            return $status['status'] === 'low_stock';
        })->count();

        return view('livewire.inventory.stocks-view', [
            'batches' => $batches,
            'categories' => $categories,
            'totalBatches' => $totalBatches,
            'totalValue' => $totalValue,
            'activeBatches' => $activeBatches,
            'expiringBatches' => $expiringBatches,
            'expiredBatches' => $expiredBatches,
            'lowStockBatches' => $lowStockBatches,
        ]);
    }
}
