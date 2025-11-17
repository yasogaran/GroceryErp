# Multi-Batch Inventory Management

## Overview

The GroceryERP system now supports **multi-batch inventory management**, allowing products to have multiple stock batches with different purchase costs and selling prices.

---

## How It Works

### 1. **Batch Creation (GRN)**

When you receive goods through a GRN:
- Each GRN item creates a new **batch** (stock movement)
- Each batch has its own:
  - **Unit Cost** (purchase price)
  - **Min Selling Price**
  - **Max Selling Price (MRP)**
  - **Batch Number**
  - **Manufacturing Date**
  - **Expiry Date**

### 2. **Viewing Available Batches**

To see all available batches for a product, use the InventoryService:

```php
$inventoryService = app(\App\Services\InventoryService::class);
$batches = $inventoryService->getAvailableBatches($product);

// Returns array of batches:
[
    [
        'stock_movement_id' => 123,
        'batch_number' => 'BATCH001',
        'grn_date' => '2025-11-15',
        'quantity_in' => 100,
        'unit_cost' => 50.00,
        'min_selling_price' => 60.00,
        'max_selling_price' => 75.00,
        'manufacturing_date' => '2025-11-01',
        'expiry_date' => '2026-11-01',
    ],
    // ... more batches
]
```

---

## POS Billing with Multiple Batches

### Default Behavior (FIFO)

By default, the system uses **FIFO (First In, First Out)**:
- When you add a product to cart without selecting a batch
- The oldest batch is automatically selected
- This ensures older stock is sold first

### Manual Batch Selection

To sell from a specific batch:

```php
// In POS, add product with specific batch
$this->addToCart($productId, $isBoxSale = false, $batchId = 123);
```

### Cart Item Structure

Each cart item now includes batch information:

```php
[
    'id' => 'uuid',
    'product_id' => 1,
    'name' => 'Product Name',
    'sku' => 'SKU001',
    'quantity' => 10,
    'unit_price' => 75.00,        // Selling price from batch
    'batch_id' => 123,            // Stock movement ID
    'batch_number' => 'BATCH001', // Batch identifier
    'batch_cost' => 50.00,        // Cost for COGS calculation
    // ... other fields
]
```

---

## Benefits

### 1. **Accurate Cost Tracking**
- Each sale knows exactly which batch it came from
- Cost of Goods Sold (COGS) is calculated using actual batch cost
- Not estimated or averaged

### 2. **Different Pricing**
- Sell different batches at different prices
- Handle price variations from suppliers
- Manage promotional pricing by batch

### 3. **Batch Traceability**
- Track which customer bought which batch
- Essential for recalls or quality issues
- Complete audit trail

### 4. **Expiry Management**
- See manufacturing and expiry dates per batch
- Sell older/expiring stock first (FIFO)
- Reduce wastage

---

## Example Scenario

### Scenario: Product with 3 Batches

**Product:** Rice 5kg

| Batch | GRN Date | Quantity | Cost | MRP | Expires |
|-------|----------|----------|------|-----|---------|
| BATCH001 | 2025-10-01 | 50 pcs | ₹45 | ₹60 | 2026-10-01 |
| BATCH002 | 2025-11-01 | 100 pcs | ₹48 | ₹65 | 2026-11-01 |
| BATCH003 | 2025-11-15 | 75 pcs | ₹50 | ₹70 | 2026-11-15 |

### Without Batch Selection (FIFO):
```php
// Customer buys 10 pcs
$this->addToCart($riceProductId);
// System automatically uses BATCH001
// Selling at ₹60, Cost ₹45
// Profit per unit: ₹15
```

### With Batch Selection:
```php
// Store wants to sell newer batch at higher price
$this->addToCart($riceProductId, false, $batch003Id);
// Selling at ₹70, Cost ₹50
// Profit per unit: ₹20
```

---

## API Methods

### InventoryService Methods

#### `getAvailableBatches(Product $product)`
Returns all batches for a product with pricing details.

#### `getBatchDetails(int $stockMovementId)`
Returns details of a specific batch.

#### `getFIFOBatch(Product $product)`
Returns the oldest batch (FIFO selection).

---

## Database Structure

### Stock Movements Table
```sql
stock_movements
├── id (batch identifier)
├── product_id
├── movement_type ('in' for new batches)
├── quantity
├── unit_cost (purchase price)
├── min_selling_price
├── max_selling_price
├── batch_number
├── manufacturing_date
├── expiry_date
└── created_at (determines FIFO order)
```

### Sale Items Table
```sql
sale_items
├── id
├── sale_id
├── product_id
├── stock_movement_id (links to batch)
├── quantity
├── unit_price (sold at)
├── unit_cost (batch cost for COGS)
└── total_price
```

---

## Future Enhancements

1. **UI for Batch Selection in POS**
   - Modal showing available batches
   - Visual indicators for expiring stock
   - Quick FIFO/LIFO toggle

2. **Batch Quantity Tracking**
   - Real-time remaining quantity per batch
   - Low stock alerts per batch
   - Automatic FIFO with quantity validation

3. **Advanced Costing Methods**
   - LIFO (Last In, First Out)
   - Weighted Average Cost
   - Specific Identification

4. **Batch Reports**
   - Batch-wise profit analysis
   - Batch movement history
   - Slow-moving batch identification

---

## Important Notes

⚠️ **Current Limitation**: The system tracks batches through stock movements but doesn't enforce quantity limits per batch. If you manually select a batch that's depleted, you may need to handle this in your business logic.

✅ **Best Practice**: Use the default FIFO behavior unless you have a specific reason to select a different batch. This ensures:
- Older stock sells first
- Less risk of expiry
- Consistent inventory rotation

---

## Testing

To test batch functionality:

1. Create multiple GRNs for the same product with different prices
2. Check `getAvailableBatches()` to see all batches
3. Add product to POS cart
4. Verify batch info is stored in cart
5. Complete sale and check `sale_items` table for batch linkage

---

## Support

For issues or questions about batch management, check:
- Stock Movements report for batch history
- Sale Items for batch allocation
- GRN records for batch creation

---

**Last Updated**: 2025-11-17
**Version**: 1.0
