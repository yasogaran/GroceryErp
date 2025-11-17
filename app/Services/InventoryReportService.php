<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * InventoryReportService - Generate inventory reports
 */
class InventoryReportService
{
    /**
     * Get stock valuation report
     */
    public function getStockValuation(): array
    {
        $products = Product::with(['stockMovements' => function ($q) {
            $q->where('quantity_remaining', '>', 0);
        }])->get();

        $totalValue = 0;
        $items = [];

        foreach ($products as $product) {
            $stockValue = $product->stockMovements->sum(function ($sm) {
                return bcmul($sm->quantity_remaining, $sm->unit_cost, 2);
            });

            if ($stockValue > 0) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $product->stockMovements->sum('quantity_remaining'),
                    'value' => $stockValue,
                ];

                $totalValue = bcadd($totalValue, $stockValue, 2);
            }
        }

        return [
            'items' => $items,
            'total_value' => $totalValue,
        ];
    }

    /**
     * Get low stock alert report
     */
    public function getLowStockAlerts(): array
    {
        $products = Product::where('stock_quantity', '<=', DB::raw('reorder_level'))
            ->where('is_active', true)
            ->get();

        return $products->toArray();
    }

    /**
     * Get out of stock report
     */
    public function getOutOfStock(): array
    {
        $products = Product::where('stock_quantity', '<=', 0)
            ->where('is_active', true)
            ->get();

        return $products->toArray();
    }

    /**
     * Get expiry alert report
     */
    public function getExpiryAlerts(int $daysAhead = 30): array
    {
        $expiryDate = now()->addDays($daysAhead);

        $batches = StockMovement::where('quantity_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $expiryDate)
            ->with('product')
            ->orderBy('expiry_date', 'asc')
            ->get();

        return $batches->toArray();
    }
}
