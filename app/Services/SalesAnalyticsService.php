<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

/**
 * SalesAnalyticsService - Generate sales analytics and reports
 */
class SalesAnalyticsService
{
    /**
     * Get sales summary for a date range
     */
    public function getSalesSummary(string $startDate, string $endDate): array
    {
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_transactions' => $sales->count(),
            'average_transaction' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        $topProducts = SaleItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_sales'))
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();

        return $topProducts->toArray();
    }

    /**
     * Get sales by payment mode
     */
    public function getSalesByPaymentMode(string $startDate, string $endDate): array
    {
        $paymentModes = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select('sale_payments.payment_mode', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_mode')
            ->get();

        return $paymentModes->toArray();
    }
}
