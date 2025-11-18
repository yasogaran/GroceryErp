<?php

namespace App\Livewire\Dashboards;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class CashierDashboard extends Component
{
    public $currentShift;
    public $shiftSales;
    public $shiftTransactions;
    public $todaySales;
    public $todayTransactions;
    public $lowStockProducts;
    public $totalCustomers;
    public $recentSales = [];
    public $topSellingToday = [];
    public $shiftPaymentBreakdown = [];

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        $user = auth()->user();

        // Current Shift Information
        $this->currentShift = $user->currentShift;

        if ($this->currentShift) {
            // Shift Metrics
            $this->shiftSales = Sale::where('shift_id', $this->currentShift->id)->sum('total_amount');
            $this->shiftTransactions = Sale::where('shift_id', $this->currentShift->id)->count();

            // Shift Payment Breakdown
            $this->shiftPaymentBreakdown = DB::table('sale_payments')
                ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
                ->where('sales.shift_id', $this->currentShift->id)
                ->select('sale_payments.payment_mode', DB::raw('SUM(sale_payments.amount) as total'))
                ->groupBy('sale_payments.payment_mode')
                ->get();
        }

        // Today's Metrics
        $this->todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $this->todayTransactions = Sale::whereDate('created_at', today())->count();

        // Product Metrics (for quick POS reference)
        $this->lowStockProducts = Product::active()
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->where('enable_low_stock_alert', true)
            ->count();

        // Customer Metrics
        $this->totalCustomers = Customer::active()->count();

        // Recent Sales (Cashier's own sales today)
        $this->recentSales = Sale::with(['customer', 'items'])
            ->where('created_by', $user->id)
            ->whereDate('created_at', today())
            ->latest()
            ->limit(10)
            ->get();

        // Top Selling Products Today
        $this->topSellingToday = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', today())
            ->select('products.name', 'products.sku', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.cashier-dashboard');
    }
}
