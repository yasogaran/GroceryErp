<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a product (stock increase).
     *
     * @param Product $product
     * @param float $quantity
     * @param array $details Additional details like reference, batch, expiry
     * @return StockMovement
     */
    public function addStock(Product $product, float $quantity, array $details = []): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $details) {
            // Increase product stock
            $product->increment('current_stock_quantity', $quantity);

            // Create stock movement record
            return StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'in',
                'quantity' => $quantity,
                'reference_type' => $details['reference_type'] ?? null,
                'reference_id' => $details['reference_id'] ?? null,
                'batch_number' => $details['batch_number'] ?? null,
                'expiry_date' => $details['expiry_date'] ?? null,
                'manufacturing_date' => $details['manufacturing_date'] ?? null,
                'performed_by' => auth()->id(),
                'notes' => $details['notes'] ?? null,
            ]);
        });
    }

    /**
     * Remove stock from a product (stock decrease).
     *
     * @param Product $product
     * @param float $quantity
     * @param array $details Additional details like reference, notes
     * @return StockMovement
     * @throws \Exception
     */
    public function removeStock(Product $product, float $quantity, array $details = []): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $details) {
            // Check if sufficient stock is available
            if ($product->current_stock_quantity < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$product->current_stock_quantity}, Required: {$quantity}");
            }

            // Decrease product stock
            $product->decrement('current_stock_quantity', $quantity);

            // Create stock movement record
            return StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'out',
                'quantity' => $quantity,
                'reference_type' => $details['reference_type'] ?? null,
                'reference_id' => $details['reference_id'] ?? null,
                'batch_number' => $details['batch_number'] ?? null,
                'performed_by' => auth()->id(),
                'notes' => $details['notes'] ?? null,
            ]);
        });
    }

    /**
     * Adjust stock (can be positive or negative).
     *
     * @param Product $product
     * @param float $quantity Positive for increase, negative for decrease
     * @param string $reason
     * @return StockMovement
     */
    public function adjustStock(Product $product, float $quantity, string $reason = ''): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $reason) {
            $oldStock = $product->current_stock_quantity;
            $newStock = $oldStock + $quantity;

            if ($newStock < 0) {
                throw new \Exception("Stock adjustment would result in negative stock");
            }

            // Update product stock
            $product->update(['current_stock_quantity' => $newStock]);

            // Create stock movement record
            return StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => abs($quantity),
                'reference_type' => 'adjustment',
                'performed_by' => auth()->id(),
                'notes' => "Stock adjusted from {$oldStock} to {$newStock}. Reason: {$reason}",
            ]);
        });
    }

    /**
     * Transfer stock to damaged stock.
     *
     * @param Product $product
     * @param float $quantity
     * @param string $reason
     * @return StockMovement
     */
    public function transferToDamaged(Product $product, float $quantity, string $reason = ''): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $reason) {
            // Check if sufficient stock is available
            if ($product->current_stock_quantity < $quantity) {
                throw new \Exception("Insufficient stock to transfer to damaged");
            }

            // Decrease current stock and increase damaged stock
            $product->decrement('current_stock_quantity', $quantity);
            $product->increment('damaged_stock_quantity', $quantity);

            // Create stock movement record
            return StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'out',
                'quantity' => $quantity,
                'reference_type' => 'damage',
                'performed_by' => auth()->id(),
                'notes' => "Transferred to damaged stock. Reason: {$reason}",
            ]);
        });
    }

    /**
     * Get stock movements for a product.
     *
     * @param Product $product
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockMovements(Product $product, int $limit = 50)
    {
        return StockMovement::where('product_id', $product->id)
            ->with('performer')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get expiring stock items.
     *
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiringStock(int $days = 30)
    {
        return StockMovement::expiringSoon($days)
            ->with('product')
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
