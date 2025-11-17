<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Customer;
use App\Models\GRN;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public $dashboardData = [];
    public $chartPeriod = '7days';

    #[Layout('components.layouts.app')]
    public function render()
    {
        $user = auth()->user();

        // Get data based on role
        $this->dashboardData = match ($user->role) {
            'admin', 'manager' => $this->getAdminDashboardData(),
            'cashier' => $this->getCashierDashboardData(),
            'store_keeper' => $this->getStoreKeeperDashboardData(),
            'accountant' => $this->getAccountantDashboardData(),
            default => $this->getDefaultDashboardData(),
        };

        return view('livewire.dashboard', [
            'data' => $this->dashboardData,
            'role' => $user->role,
        ]);
    }

    /**
     * Get dashboard data for Admin/Manager roles
     */
    private function getAdminDashboardData(): array
    {
        return Cache::remember('dashboard_admin_' . $this->chartPeriod, 300, function () {
            $today = today();
            $yesterday = $today->copy()->subDay();

            // Today's sales
            $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
            $todayTransactions = Sale::whereDate('sale_date', $today)->count();
            $yesterdaySales = Sale::whereDate('sale_date', $yesterday)->sum('total_amount');

            // Customer metrics
            $totalCustomers = Customer::where('is_active', true)->count();
            $newCustomersThisMonth = Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $lastMonthCustomers = Customer::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();

            // Stock metrics
            $stockValue = DB::table('products')
                ->selectRaw('SUM(current_stock_quantity * min_selling_price) as value')
                ->where('is_active', true)
                ->value('value') ?? 0;

            $lowStockCount = Product::where('is_active', true)
                ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                ->count();

            $outOfStockCount = Product::where('is_active', true)
                ->where('current_stock_quantity', '<=', 0)
                ->count();

            // Pending items
            $pendingGRNs = GRN::where('status', 'pending')->count();
            $pendingReturns = DB::table('sale_returns')
                ->where('status', 'pending')
                ->count();

            // Financial overview
            $cashInHand = $this->getCashInHand();
            $bankBalance = $this->getBankBalance();
            $accountsReceivable = Customer::sum('total_purchases');
            $accountsPayable = Supplier::sum('total_purchases');

            return [
                'todaySales' => $todaySales,
                'todayTransactions' => $todayTransactions,
                'yesterdaySales' => $yesterdaySales,
                'salesChange' => $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0,
                'totalCustomers' => $totalCustomers,
                'newCustomersThisMonth' => $newCustomersThisMonth,
                'customerGrowth' => $lastMonthCustomers > 0 ? (($newCustomersThisMonth - $lastMonthCustomers) / $lastMonthCustomers) * 100 : 0,
                'stockValue' => $stockValue,
                'lowStockCount' => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
                'pendingGRNs' => $pendingGRNs,
                'pendingReturns' => $pendingReturns,
                'totalPendingActions' => $pendingGRNs + $pendingReturns,
                'cashInHand' => $cashInHand,
                'bankBalance' => $bankBalance,
                'accountsReceivable' => $accountsReceivable,
                'accountsPayable' => $accountsPayable,
                'salesChartData' => $this->getSalesChartData($this->chartPeriod),
                'topProducts' => $this->getTopProducts(10),
                'recentSales' => Sale::with('customer', 'cashier')
                    ->latest('sale_date')
                    ->take(10)
                    ->get(),
            ];
        });
    }

    /**
     * Get dashboard data for Cashier role
     */
    private function getCashierDashboardData(): array
    {
        $user = auth()->user();
        $currentShift = Shift::where('cashier_id', $user->id)
            ->whereNull('shift_end')
            ->first();

        $todaySales = Sale::where('created_by', $user->id)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $todayTransactions = Sale::where('created_by', $user->id)
            ->whereDate('sale_date', today())
            ->count();

        $avgTransactionValue = $todayTransactions > 0 ? $todaySales / $todayTransactions : 0;

        return [
            'currentShift' => $currentShift,
            'hasOpenShift' => $currentShift !== null,
            'todaySales' => $todaySales,
            'todayTransactions' => $todayTransactions,
            'avgTransactionValue' => $avgTransactionValue,
            'recentSales' => Sale::where('created_by', $user->id)
                ->with('customer')
                ->latest('sale_date')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Get dashboard data for Store Keeper role
     */
    private function getStoreKeeperDashboardData(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
            ->count();
        $outOfStockCount = Product::where('is_active', true)
            ->where('current_stock_quantity', '<=', 0)
            ->count();

        // Products expiring soon (if you have expiry tracking)
        $expiringSoon = 0; // Placeholder - implement if you have expiry date tracking

        return [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'expiringSoon' => $expiringSoon,
            'recentStockMovements' => StockMovement::with('product')
                ->latest()
                ->take(10)
                ->get(),
            'lowStockProducts' => Product::where('is_active', true)
                ->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                ->orderBy('current_stock_quantity', 'asc')
                ->take(10)
                ->get(),
        ];
    }

    /**
     * Get dashboard data for Accountant role
     */
    private function getAccountantDashboardData(): array
    {
        $todayIncome = Sale::whereDate('sale_date', today())->sum('total_amount');

        // Get today's expenses if you have an Expense model
        $todayExpenses = 0; // Placeholder

        $netProfit = $todayIncome - $todayExpenses;

        $pendingGRNs = GRN::where('status', 'pending')->count();
        $cashPosition = $this->getCashInHand() + $this->getBankBalance();

        return [
            'todayIncome' => $todayIncome,
            'todayExpenses' => $todayExpenses,
            'netProfit' => $netProfit,
            'pendingGRNs' => $pendingGRNs,
            'cashPosition' => $cashPosition,
            'cashInHand' => $this->getCashInHand(),
            'bankBalance' => $this->getBankBalance(),
            'incomeExpenseChart' => $this->getIncomeExpenseChartData(30),
        ];
    }

    /**
     * Default dashboard data for other roles
     */
    private function getDefaultDashboardData(): array
    {
        return [
            'todaySales' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'todayTransactions' => Sale::whereDate('sale_date', today())->count(),
            'totalCustomers' => Customer::where('is_active', true)->count(),
            'totalProducts' => Product::where('is_active', true)->count(),
        ];
    }

    /**
     * Get sales chart data for specified period
     */
    private function getSalesChartData(string $period = '7days'): array
    {
        $days = match ($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            '365days' => 365,
            default => 7,
        };

        $sales = Sale::selectRaw('DATE(sale_date) as date, SUM(total_amount) as total')
            ->where('sale_date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero values
        $labels = [];
        $data = [];
        $salesMap = $sales->pluck('total', 'date')->toArray();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $data[] = $salesMap[$date] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(int $limit = 10): array
    {
        return SaleItem::select(
            'product_id',
            DB::raw('SUM(quantity) as units_sold'),
            DB::raw('SUM(subtotal) as revenue')
        )
            ->with('product')
            ->whereHas('sale', function ($query) {
                $query->whereDate('sale_date', today());
            })
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'units_sold' => $item->units_sold,
                    'revenue' => $item->revenue,
                ];
            })
            ->toArray();
    }

    /**
     * Get cash in hand (from open and closed shifts)
     */
    private function getCashInHand(): float
    {
        // Sum of all opening cash from open shifts + closing cash from closed shifts today
        $openShifts = Shift::whereNull('shift_end')->sum('opening_cash');
        $closedShiftsToday = Shift::whereDate('shift_end', today())->sum('closing_cash');

        return $openShifts + $closedShiftsToday;
    }

    /**
     * Get total bank balance from all bank accounts
     */
    private function getBankBalance(): float
    {
        return Account::where('account_type', 'asset')
            ->where('account_name', 'like', '%bank%')
            ->sum('balance') ?? 0;
    }

    /**
     * Get income vs expense chart data
     */
    private function getIncomeExpenseChartData(int $days = 30): array
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');

            $income = Sale::whereDate('sale_date', $date)->sum('total_amount');
            $incomeData[] = $income;

            // Expenses would come from an Expense model
            $expenseData[] = 0; // Placeholder
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Update chart period
     */
    public function updateChartPeriod($period)
    {
        $this->chartPeriod = $period;
        Cache::forget('dashboard_admin_' . $this->chartPeriod);
    }
}
