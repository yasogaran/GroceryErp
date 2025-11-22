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
     * @param array $details ['reference_type' => '', 'reference_id' => '', 'batch_number' => '', 'expiry_date' => '', 'unit_cost' => '', 'min_selling_price' => '', 'max_selling_price' => '', 'notes' => '']
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

            // Update product selling prices if provided
            if (isset($details['min_selling_price']) || isset($details['max_selling_price'])) {
                $updateData = [];
                if (isset($details['min_selling_price'])) {
                    $updateData['min_selling_price'] = $details['min_selling_price'];
                }
                if (isset($details['max_selling_price'])) {
                    $updateData['max_selling_price'] = $details['max_selling_price'];
                }
                if (!empty($updateData)) {
                    $product->update($updateData);
                }
            }

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
                'unit_cost' => $details['unit_cost'] ?? null,
                'min_selling_price' => $details['min_selling_price'] ?? null,
                'max_selling_price' => $details['max_selling_price'] ?? null,
                'performed_by' => auth()->id(),
                'notes' => $details['notes'] ?? null,
            ]);

            return $movement;
        });
    }

    /**
     * Remove stock from a product (stock decrease).
     *
     * @param Product $product
     * @param float $quantity
     * @param array $details Additional details like reference, notes, pricing
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

            // Get pricing from FIFO batch if not provided
            if (!isset($details['unit_cost']) || !isset($details['min_selling_price']) || !isset($details['max_selling_price'])) {
                $fifoBatch = $this->getFIFOBatch($product);
                $details['unit_cost'] = $details['unit_cost'] ?? $fifoBatch['unit_cost'] ?? null;
                $details['min_selling_price'] = $details['min_selling_price'] ?? $fifoBatch['min_selling_price'] ?? $product->min_selling_price;
                $details['max_selling_price'] = $details['max_selling_price'] ?? $fifoBatch['max_selling_price'] ?? $product->max_selling_price;
                $details['batch_number'] = $details['batch_number'] ?? $fifoBatch['batch_number'] ?? null;
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
                'unit_cost' => $details['unit_cost'] ?? null,
                'min_selling_price' => $details['min_selling_price'] ?? null,
                'max_selling_price' => $details['max_selling_price'] ?? null,
                'performed_by' => auth()->id(),
                'notes' => $details['notes'] ?? null,
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
     * @param array $details ['reference_type' => '', 'reference_id' => '', 'notes' => '', pricing details]
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
            // Get FIFO batch for pricing and tracking
            $sourceBatchId = $details['source_stock_movement_id'] ?? null;

            if (!isset($details['unit_cost']) || !isset($details['min_selling_price']) || !isset($details['max_selling_price']) || !$sourceBatchId) {
                $fifoBatch = $this->getFIFOBatch($product);
                $details['unit_cost'] = $details['unit_cost'] ?? $fifoBatch['unit_cost'] ?? null;
                $details['min_selling_price'] = $details['min_selling_price'] ?? $fifoBatch['min_selling_price'] ?? $product->min_selling_price;
                $details['max_selling_price'] = $details['max_selling_price'] ?? $fifoBatch['max_selling_price'] ?? $product->max_selling_price;
                $details['batch_number'] = $details['batch_number'] ?? $fifoBatch['batch_number'] ?? null;
                // Use FIFO batch as source if not specified
                $sourceBatchId = $sourceBatchId ?? $fifoBatch['stock_movement_id'] ?? null;
            }

            // Decrement product stock
            $product->decrement('current_stock_quantity', $quantity);

            // Create stock movement record (negative quantity for OUT)
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'out',
                'quantity' => -$quantity, // Negative for OUT
                'reference_type' => $details['reference_type'] ?? null,
                'reference_id' => $details['reference_id'] ?? null,
                'source_stock_movement_id' => $sourceBatchId, // Track which batch this depletes
                'batch_number' => $details['batch_number'] ?? null,
                'expiry_date' => $details['expiry_date'] ?? null,
                'unit_cost' => $details['unit_cost'] ?? null,
                'min_selling_price' => $details['min_selling_price'] ?? null,
                'max_selling_price' => $details['max_selling_price'] ?? null,
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
     * @param string|array $reasonOrDetails
     * @return StockMovement
     * @throws Exception
     */
    public function markAsDamaged(Product $product, float $quantity, $reasonOrDetails = null): StockMovement
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

        // Handle both string and array input for backward compatibility
        $details = is_array($reasonOrDetails) ? $reasonOrDetails : ['notes' => $reasonOrDetails];

        return DB::transaction(function () use ($product, $quantity, $details) {
            // Get FIFO batch for pricing and tracking
            $sourceBatchId = $details['source_stock_movement_id'] ?? null;

            if (!isset($details['unit_cost']) || !isset($details['min_selling_price']) || !isset($details['max_selling_price']) || !$sourceBatchId) {
                $fifoBatch = $this->getFIFOBatch($product);
                $details['unit_cost'] = $details['unit_cost'] ?? $fifoBatch['unit_cost'] ?? null;
                $details['min_selling_price'] = $details['min_selling_price'] ?? $fifoBatch['min_selling_price'] ?? $product->min_selling_price;
                $details['max_selling_price'] = $details['max_selling_price'] ?? $fifoBatch['max_selling_price'] ?? $product->max_selling_price;
                $details['batch_number'] = $details['batch_number'] ?? $fifoBatch['batch_number'] ?? null;
                // Use FIFO batch as source if not specified
                $sourceBatchId = $sourceBatchId ?? $fifoBatch['stock_movement_id'] ?? null;
            }

            // Decrement current stock
            $product->decrement('current_stock_quantity', $quantity);

            // Increment damaged stock
            $product->increment('damaged_stock_quantity', $quantity);

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'damage',
                'quantity' => -$quantity, // Negative because it's removed from current stock
                'reference_type' => $details['reference_type'] ?? null,
                'reference_id' => $details['reference_id'] ?? null,
                'source_stock_movement_id' => $sourceBatchId, // Track which batch this depletes
                'batch_number' => $details['batch_number'] ?? null,
                'expiry_date' => null,
                'unit_cost' => $details['unit_cost'] ?? null,
                'min_selling_price' => $details['min_selling_price'] ?? null,
                'max_selling_price' => $details['max_selling_price'] ?? null,
                'notes' => $details['notes'] ?? 'Stock marked as damaged',
                'performed_by' => Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Write-off damaged stock (remove from inventory completely).
     *
     * @param Product $product
     * @param float $quantity
     * @param string $reason
     * @return StockMovement
     * @throws Exception
     */
    public function writeOffDamaged(Product $product, float $quantity, string $reason): StockMovement
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        // Check if sufficient damaged stock available
        if ($product->damaged_stock_quantity < $quantity) {
            throw new Exception(
                "Insufficient damaged stock to write-off for product '{$product->name}'. " .
                "Available: {$product->damaged_stock_quantity}, Requested: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $reason) {
            // Decrement damaged stock
            $product->decrement('damaged_stock_quantity', $quantity);

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'write_off',
                'quantity' => -$quantity,
                'reference_type' => null,
                'reference_id' => null,
                'batch_number' => null,
                'expiry_date' => null,
                'notes' => 'Write-off damaged stock: ' . $reason,
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

    /**
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

    /**
     * Get the oldest stock batch (FIFO - First In First Out) for a product.
     * Returns pricing details from the oldest stock IN movement.
     *
     * @param Product $product
     * @return array ['unit_cost' => float|null, 'min_selling_price' => float|null, 'max_selling_price' => float|null, 'batch_number' => string|null]
     */
    public function getFIFOBatch(Product $product): array
    {
        // Get the oldest stock IN movement with pricing information
        $oldestBatch = StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'in')
            ->whereNotNull('unit_cost')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($oldestBatch) {
            return [
                'unit_cost' => $oldestBatch->unit_cost,
                'min_selling_price' => $oldestBatch->min_selling_price,
                'max_selling_price' => $oldestBatch->max_selling_price,
                'batch_number' => $oldestBatch->batch_number,
                'stock_movement_id' => $oldestBatch->id,
            ];
        }

        // Fallback to current product prices if no batch found
        return [
            'unit_cost' => null,
            'min_selling_price' => $product->min_selling_price,
            'max_selling_price' => $product->max_selling_price,
            'batch_number' => null,
            'stock_movement_id' => null,
        ];
    }

    /**
     * Get all available stock batches for a product with remaining quantities.
     * This helps in multi-batch inventory management.
     *
     * @param Product $product
     * @return array Array of batches with details
     */
    public function getAvailableBatches(Product $product): array
    {
        // Get all stock IN movements (these represent batches)
        $stockInMovements = StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'in')
            ->whereNotNull('unit_cost')
            ->orderBy('created_at', 'asc')
            ->get();

        $batches = [];

        foreach ($stockInMovements as $movement) {
            // For simplicity, we're grouping by batch_number + unit_cost + prices
            // In reality, each stock IN movement is a separate batch
            $batchKey = $movement->id;

            // Get supplier name if available (from GRN)
            $supplierName = null;
            if ($movement->reference_type === 'App\\Models\\GRN' && $movement->reference_id) {
                // Load GRN with supplier only when needed
                $grn = \App\Models\GRN::with('supplier')->find($movement->reference_id);
                if ($grn && $grn->supplier) {
                    $supplierName = $grn->supplier->name;
                }
            }

            $batches[] = [
                'stock_movement_id' => $movement->id,
                'batch_number' => $movement->batch_number ?? 'N/A',
                'grn_date' => $movement->created_at->format('Y-m-d'),
                'quantity_in' => $movement->quantity,
                'unit_cost' => $movement->unit_cost,
                'min_selling_price' => $movement->min_selling_price,
                'max_selling_price' => $movement->max_selling_price,
                'manufacturing_date' => $movement->manufacturing_date?->format('Y-m-d'),
                'expiry_date' => $movement->expiry_date?->format('Y-m-d'),
                'supplier_name' => $supplierName,
            ];
        }

        return $batches;
    }

    /**
     * Get specific batch details by stock movement ID.
     *
     * @param int $stockMovementId
     * @return array|null
     */
    public function getBatchDetails(int $stockMovementId): ?array
    {
        $movement = StockMovement::find($stockMovementId);

        if (!$movement || $movement->movement_type !== 'in') {
            return null;
        }

        // Get supplier name if available (from GRN)
        $supplierName = null;
        if ($movement->reference_type === 'App\\Models\\GRN' && $movement->reference_id) {
            // Load GRN with supplier only when needed
            $grn = \App\Models\GRN::with('supplier')->find($movement->reference_id);
            if ($grn && $grn->supplier) {
                $supplierName = $grn->supplier->name;
            }
        }

        return [
            'stock_movement_id' => $movement->id,
            'batch_number' => $movement->batch_number ?? 'N/A',
            'unit_cost' => $movement->unit_cost,
            'min_selling_price' => $movement->min_selling_price,
            'max_selling_price' => $movement->max_selling_price,
            'grn_date' => $movement->created_at->format('Y-m-d'),
            'manufacturing_date' => $movement->manufacturing_date?->format('Y-m-d'),
            'expiry_date' => $movement->expiry_date?->format('Y-m-d'),
            'supplier_name' => $supplierName,
        ];
    }
}
