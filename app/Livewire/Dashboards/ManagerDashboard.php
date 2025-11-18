<?php

namespace App\Livewire\Dashboards;

use App\Models\Customer;
use App\Models\GRN;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ManagerDashboard extends Component
{
    public $todaySales;
    public $weekSales;
    public $monthSales;
    public $totalProducts;
    public $lowStockProducts;
    public $outOfStockProducts;
    public $pendingGRNs;
    public $supplierOutstanding;
    public $topSellingProducts = [];
    public $stockAlerts = [];
    public $recentGRNs = [];

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        // Sales Metrics
        $this->todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $this->weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount');
        $this->monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Product & Inventory Metrics
        $this->totalProducts = Product::active()->count();
        $this->lowStockProducts = Product::active()
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->where('current_stock_quantity', '>', 0)
            ->where('enable_low_stock_alert', true)
            ->count();
        $this->outOfStockProducts = Product::active()
            ->where('current_stock_quantity', '<=', 0)
            ->count();

        // GRN & Supplier Metrics
        $this->pendingGRNs = GRN::where('status', 'draft')->count();
        $this->supplierOutstanding = Supplier::sum('outstanding_balance');

        // Top Selling Products (Last 7 days)
        $this->topSellingProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.created_at', '>=', now()->subDays(7))
            ->select('products.name', 'products.sku', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.total_price) as revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(8)
            ->get();

        // Stock Alerts
        $this->stockAlerts = Product::active()
            ->where(function($query) {
                $query->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                    ->orWhere('current_stock_quantity', '<=', 0);
            })
            ->select('name', 'current_stock_quantity', 'reorder_level', 'sku')
            ->orderBy('current_stock_quantity', 'asc')
            ->limit(10)
            ->get();

        // Recent GRNs
        $this->recentGRNs = GRN::with(['supplier', 'creator'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.manager-dashboard');
    }
}
