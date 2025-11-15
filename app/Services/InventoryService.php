<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a product
     */
    public function addStock(Product $product, float $quantity, array $options = [])
    {
        DB::transaction(function () use ($product, $quantity, $options) {
            // Update product stock
            $product->increment('current_stock_quantity', $quantity);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => $options['movement_type'] ?? 'in',
                'quantity' => $quantity,
                'unit_cost' => $options['unit_cost'] ?? null,
                'batch_number' => $options['batch_number'] ?? null,
                'expiry_date' => $options['expiry_date'] ?? null,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'notes' => $options['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return $product->fresh();
    }

    /**
     * Reduce stock from a product
     */
    public function reduceStock(Product $product, float $quantity, array $options = [])
    {
        DB::transaction(function () use ($product, $quantity, $options) {
            // Check if sufficient stock is available
            if ($product->current_stock_quantity < $quantity) {
                throw new Exception("Insufficient stock for product: {$product->name}. Available: {$product->current_stock_quantity}, Required: {$quantity}");
            }

            // Update product stock
            $product->decrement('current_stock_quantity', $quantity);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => $options['movement_type'] ?? 'out',
                'quantity' => $quantity,
                'unit_cost' => $options['unit_cost'] ?? null,
                'batch_number' => $options['batch_number'] ?? null,
                'expiry_date' => $options['expiry_date'] ?? null,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'notes' => $options['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return $product->fresh();
    }

    /**
     * Mark stock as damaged
     */
    public function markAsDamaged(Product $product, float $quantity, ?string $notes = null)
    {
        DB::transaction(function () use ($product, $quantity, $notes) {
            // Check if sufficient stock is available
            if ($product->current_stock_quantity < $quantity) {
                throw new Exception("Insufficient stock to mark as damaged. Available: {$product->current_stock_quantity}, Requested: {$quantity}");
            }

            // Update product stock
            $product->decrement('current_stock_quantity', $quantity);
            $product->increment('damaged_stock_quantity', $quantity);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'damage',
                'quantity' => $quantity,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);
        });

        return $product->fresh();
    }

    /**
     * Adjust stock (manual correction)
     */
    public function adjustStock(Product $product, float $newQuantity, ?string $notes = null)
    {
        DB::transaction(function () use ($product, $newQuantity, $notes) {
            $currentQuantity = $product->current_stock_quantity;
            $difference = $newQuantity - $currentQuantity;

            // Update product stock
            $product->update(['current_stock_quantity' => $newQuantity]);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => abs($difference),
                'notes' => $notes ?? "Stock adjusted from {$currentQuantity} to {$newQuantity}",
                'created_by' => auth()->id(),
            ]);
        });

        return $product->fresh();
    }

    /**
     * Get stock movement history for a product
     */
    public function getStockHistory(Product $product, int $limit = 50)
    {
        return StockMovement::where('product_id', $product->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
