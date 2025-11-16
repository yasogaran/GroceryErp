<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventoryService
{
    /**
     * Add stock to a product.
     *
     * @param Product $product
     * @param float $quantity
     * @param array $details ['reference_type' => '', 'reference_id' => '', 'batch_number' => '', 'expiry_date' => '', 'notes' => '']
     * @return StockMovement
     * @throws Exception
     */
    public function addStock(Product $product, float $quantity, array $details = []): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($product, $quantity, $details) {
            // Increment product stock
            $product->increment('current_stock_quantity', $quantity);

            // Create stock movement record
            $movement = StockMovement::create([
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
                'notes' => $details['notes'] ?? null,
                'performed_by' => $details['performed_by'] ?? Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Reduce stock from a product.
     *
     * @param Product $product
     * @param float $quantity
     * @param array $details ['reference_type' => '', 'reference_id' => '', 'notes' => '']
     * @return StockMovement
     * @throws Exception
     */
    public function reduceStock(Product $product, float $quantity, array $details = []): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        // Check if sufficient stock available
        if ($product->current_stock_quantity < $quantity) {
            throw new Exception(
                "Insufficient stock for product '{$product->name}'. " .
                "Available: {$product->current_stock_quantity}, Requested: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $details) {
            // Decrement product stock
            $product->decrement('current_stock_quantity', $quantity);

            // Create stock movement record (negative quantity for OUT)
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'out',
                'quantity' => -$quantity, // Negative for OUT
                'reference_type' => $details['reference_type'] ?? null,
                'reference_id' => $details['reference_id'] ?? null,
                'batch_number' => $details['batch_number'] ?? null,
                'expiry_date' => $details['expiry_date'] ?? null,
                'notes' => $details['notes'] ?? null,
                'performed_by' => $details['performed_by'] ?? Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Mark stock as damaged.
     *
     * @param Product $product
     * @param float $quantity
     * @param string|null $reason
     * @return StockMovement
     * @throws Exception
     */
    public function markAsDamaged(Product $product, float $quantity, ?string $reason = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        // Check if sufficient stock available
        if ($product->current_stock_quantity < $quantity) {
            throw new Exception(
                "Insufficient stock to mark as damaged for product '{$product->name}'. " .
                "Available: {$product->current_stock_quantity}, Requested: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $reason) {
            // Decrement current stock
            $product->decrement('current_stock_quantity', $quantity);

            // Increment damaged stock
            $product->increment('damaged_stock_quantity', $quantity);

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'damage',
                'quantity' => -$quantity, // Negative because it's removed from current stock
                'reference_type' => null,
                'reference_id' => null,
                'batch_number' => null,
                'expiry_date' => null,
                'notes' => $reason ?? 'Stock marked as damaged',
                'performed_by' => Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Adjust stock manually (for stock corrections).
     *
     * @param Product $product
     * @param float $quantity (positive to add, negative to reduce)
     * @param string $reason
     * @return StockMovement
     * @throws Exception
     */
    public function adjustStock(Product $product, float $quantity, string $reason): StockMovement
    {
        if ($quantity == 0) {
            throw new Exception('Adjustment quantity cannot be zero.');
        }

        // If reducing stock, check if sufficient stock available
        if ($quantity < 0 && $product->current_stock_quantity < abs($quantity)) {
            throw new Exception(
                "Cannot reduce stock below zero for product '{$product->name}'. " .
                "Current stock: {$product->current_stock_quantity}, Adjustment: " . abs($quantity)
            );
        }

        return DB::transaction(function () use ($product, $quantity, $reason) {
            // Update product stock (increment for positive, decrement for negative)
            if ($quantity > 0) {
                $product->increment('current_stock_quantity', $quantity);
            } else {
                $product->decrement('current_stock_quantity', abs($quantity));
            }

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => $quantity,
                'reference_type' => 'adjustment',
                'reference_id' => null,
                'batch_number' => null,
                'expiry_date' => null,
                'notes' => $reason,
                'performed_by' => Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Get stock movement history for a product.
     *
     * @param Product $product
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockMovements(Product $product, int $limit = 50)
    {
        return StockMovement::where('product_id', $product->id)
            ->with('performer')
          ->get();
      }
  
    public function getStockHistory(Product $product, int $limit = 50)
    {
        return StockMovement::where('product_id', $product->id)
            ->with('performedBy')
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
     * Calculate total stock in for a product within a date range.
     *
     * @param Product $product
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalStockIn(Product $product, string $startDate, string $endDate): float
    {
        return (float) StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'in')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');
    }

    /**
     * Calculate total stock out for a product within a date range.
     *
     * @param Product $product
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalStockOut(Product $product, string $startDate, string $endDate): float
    {
        return (float) abs(StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'out')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity'));
    }
}
