<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;

class DailySalesReport extends Component
{
    public $reportDate;

    public function mount()
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $sales = Sale::with(['items.product', 'customer', 'cashier'])
            ->whereDate('sale_date', $this->reportDate)
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'total_transactions' => $sales->count(),
            'cash_sales' => SalePayment::whereIn('sale_id', $sales->pluck('id'))
                ->where('payment_mode', 'cash')
                ->sum('amount'),
            'avg_transaction' => $sales->count() > 0
                ? $sales->sum('total_amount') / $sales->count()
                : 0,
        ];

        // Top selling products
        $topProducts = SaleItem::whereIn('sale_id', $sales->pluck('id'))
            ->select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total_price) as total_value'))
            ->groupBy('product_id')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->with('product')
            ->get();

        return view('livewire.reports.daily-sales-report', [
            'sales' => $sales,
            'summary' => $summary,
            'topProducts' => $topProducts,
        ]);
    }
}
