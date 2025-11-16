<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    /**
     * Calculate refund amount for returned item
     * Handles box discount adjustment
     */
    public function calculateRefundAmount(SaleItem $saleItem, float $returnedQuantity): float
    {
        // Get original item details
        $originalQuantity = $saleItem->quantity;
        $originalTotal = $saleItem->total_price;

        // Calculate per-piece price (including discount adjustment)
        $pricePerPiece = ($originalTotal) / $originalQuantity;

        // Calculate refund
        $refundAmount = $pricePerPiece * $returnedQuantity;

        return round($refundAmount, 2);
    }

    /**
     * Process complete return
     */
    public function processReturn(array $data): SaleReturn
    {
        return DB::transaction(function () use ($data) {
            // 1. Create return record
            $return = SaleReturn::create([
                'return_number' => SaleReturn::generateReturnNumber(),
                'original_sale_id' => $data['sale_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'return_date' => now(),
                'total_refund_amount' => $data['total_refund_amount'],
                'refund_mode' => $data['refund_mode'],
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // 2. Process each returned item
            foreach ($data['items'] as $itemData) {
                // Create return item record
                $returnItem = SaleReturnItem::create([
                    'return_id' => $return->id,
                    'sale_item_id' => $itemData['sale_item_id'],
                    'product_id' => $itemData['product_id'],
                    'returned_quantity' => $itemData['quantity'],
                    'refund_amount' => $itemData['refund_amount'],
                    'is_damaged' => $itemData['is_damaged'] ?? false,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Update stock based on damaged status
                $product = Product::find($itemData['product_id']);

                if ($itemData['is_damaged']) {
                    // Add to damaged stock
                    app(InventoryService::class)->markAsDamaged(
                        $product,
                        $itemData['quantity'],
                        'Returned as damaged from return #' . $return->return_number . ': ' . ($itemData['notes'] ?? 'No reason specified')
                    );
                } else {
                    // Restock non-damaged items
                    app(InventoryService::class)->addStock($product, $itemData['quantity'], [
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'notes' => 'Restocked from sale return #' . $return->return_number,
                    ]);
                }
            }

            // 3. Update shift totals (reduce cash/bank sales)
            $sale = Sale::find($data['sale_id']);
            if ($sale && $sale->shift) {
                $shift = $sale->shift;
                $shift->decrement('total_sales', $data['total_refund_amount']);

                if ($data['refund_mode'] === 'cash') {
                    $shift->decrement('total_cash_sales', $data['total_refund_amount']);
                } else {
                    $shift->decrement('total_bank_sales', $data['total_refund_amount']);
                }
            }

            // 4. Update original sale status
            $this->updateSaleStatus($sale);

            return $return;
        });
    }

    /**
     * Update sale status based on returns
     */
    private function updateSaleStatus(Sale $sale)
    {
        $totalSaleAmount = $sale->total_amount;
        $totalReturned = $sale->returns()->sum('total_refund_amount');

        if ($totalReturned >= $totalSaleAmount) {
            $sale->update(['status' => 'returned']);
        } else if ($totalReturned > 0) {
            $sale->update(['status' => 'partially_returned']);
        }
    }

    /**
     * Validate return quantities
     */
    public function validateReturnQuantities(Sale $sale, array $items): array
    {
        $errors = [];

        foreach ($items as $itemData) {
            $saleItem = $sale->items()->find($itemData['sale_item_id']);

            if (!$saleItem) {
                $errors[] = "Sale item not found";
                continue;
            }

            // Calculate already returned quantity
            $alreadyReturned = SaleReturnItem::where('sale_item_id', $saleItem->id)
                ->sum('returned_quantity');

            $remainingQuantity = $saleItem->quantity - $alreadyReturned;

            if ($itemData['quantity'] > $remainingQuantity) {
                $errors[] = "Cannot return {$itemData['quantity']} of {$saleItem->product->name}. Only {$remainingQuantity} remaining.";
            }
        }

        return $errors;
    }

    /**
     * Get return summary for a date range
     */
    public function getReturnSummary(string $startDate, string $endDate): array
    {
        $returns = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->with(['items.product'])
            ->get();

        return [
            'total_returns' => $returns->count(),
            'total_refund_amount' => $returns->sum('total_refund_amount'),
            'cash_refunds' => $returns->where('refund_mode', 'cash')->sum('total_refund_amount'),
            'bank_refunds' => $returns->where('refund_mode', 'bank_transfer')->sum('total_refund_amount'),
            'damaged_items_count' => SaleReturnItem::whereHas('return', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate]);
            })->where('is_damaged', true)->sum('returned_quantity'),
            'non_damaged_items_count' => SaleReturnItem::whereHas('return', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate]);
            })->where('is_damaged', false)->sum('returned_quantity'),
        ];
    }
}
