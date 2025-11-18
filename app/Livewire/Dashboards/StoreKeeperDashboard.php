<?php

namespace App\Livewire\Dashboards;

use App\Models\GRN;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class StoreKeeperDashboard extends Component
{
    public $totalProducts;
    public $activeProducts;
    public $lowStockProducts;
    public $outOfStockProducts;
    public $damagedStockValue;
    public $pendingGRNs;
    public $todayStockIn;
    public $todayStockOut;
    public $stockAlerts = [];
    public $recentStockMovements = [];
    public $recentGRNs = [];
    public $damagedProducts = [];

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        // Product Metrics
        $this->totalProducts = Product::count();
        $this->activeProducts = Product::active()->count();
        $this->lowStockProducts = Product::active()
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->where('current_stock_quantity', '>', 0)
            ->where('enable_low_stock_alert', true)
            ->count();
        $this->outOfStockProducts = Product::active()
            ->where('current_stock_quantity', '<=', 0)
            ->count();

        // Damaged Stock Value
        $this->damagedStockValue = Product::active()
            ->where('damaged_stock_quantity', '>', 0)
            ->get()
            ->sum(function($product) {
                return $product->getDamagedStockValue();
            });

        // GRN Metrics
        $this->pendingGRNs = GRN::where('status', 'draft')->count();

        // Today's Stock Movement
        $this->todayStockIn = StockMovement::whereDate('created_at', today())
            ->where('movement_type', 'in')
            ->sum('quantity');
        $this->todayStockOut = StockMovement::whereDate('created_at', today())
            ->where('movement_type', 'out')
            ->sum(DB::raw('ABS(quantity)'));

        // Stock Alerts
        $this->stockAlerts = Product::active()
            ->where(function($query) {
                $query->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                    ->orWhere('current_stock_quantity', '<=', 0);
            })
            ->select('name', 'current_stock_quantity', 'reorder_level', 'sku')
            ->orderBy('current_stock_quantity', 'asc')
            ->limit(15)
            ->get();

        // Recent Stock Movements
        $this->recentStockMovements = StockMovement::with(['product', 'performedBy'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        // Recent GRNs
        $this->recentGRNs = GRN::with(['supplier', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        // Damaged Products
        $this->damagedProducts = Product::active()
            ->where('damaged_stock_quantity', '>', 0)
            ->select('name', 'sku', 'damaged_stock_quantity', 'current_stock_quantity')
            ->orderBy('damaged_stock_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.store-keeper-dashboard');
    }
}
