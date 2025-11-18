<?php

namespace App\Livewire\Dashboards;

use App\Models\Customer;
use App\Models\GRN;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SalesAnalyticsService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class AdminDashboard extends Component
{
    public $todaySales;
    public $monthSales;
    public $totalProducts;
    public $lowStockProducts;
    public $totalCustomers;
    public $totalSuppliers;
    public $totalUsers;
    public $pendingGRNs;
    public $topSellingProducts = [];
    public $recentSales = [];
    public $stockAlerts = [];
    public $salesByPaymentMode = [];

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        // Sales Metrics
        $this->todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $this->monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Product Metrics
        $this->totalProducts = Product::active()->count();
        $this->lowStockProducts = Product::active()
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->where('enable_low_stock_alert', true)
            ->count();

        // Customer & Supplier Metrics
        $this->totalCustomers = Customer::active()->count();
        $this->totalSuppliers = Supplier::active()->count();

        // User Metrics
        $this->totalUsers = User::active()->count();

        // GRN Metrics
        $this->pendingGRNs = GRN::where('status', 'draft')->count();

        // Top Selling Products (Last 30 days)
        $this->topSellingProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.created_at', '>=', now()->subDays(30))
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.subtotal) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Recent Sales (Last 10)
        $this->recentSales = Sale::with(['customer', 'cashier'])
            ->latest()
            ->limit(10)
            ->get();

        // Stock Alerts
        $this->stockAlerts = Product::active()
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->where('enable_low_stock_alert', true)
            ->select('name', 'current_stock_quantity', 'reorder_level', 'sku')
            ->limit(10)
            ->get();

        // Sales by Payment Mode (Today)
        $this->salesByPaymentMode = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereDate('sales.created_at', today())
            ->select('sale_payments.payment_mode', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_mode')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.admin-dashboard');
    }
}
