# GroceryERP - Services Documentation

> Complete reference guide for all services in the GroceryERP system

**Version:** 1.0
**Last Updated:** 2025-11-22

---

## Table of Contents

1. [Service Layer Overview](#service-layer-overview)
2. [InventoryService](#inventoryservice)
3. [POSService](#posservice)
4. [OfferService](#offerservice)
5. [LoyaltyService](#loyaltyservice)
6. [TransactionService](#transactionservice)
7. [ReturnService](#returnservice)
8. [ShiftService](#shiftservice)
9. [BarcodeService](#barcodeservice)
10. [PrintService](#printservice)
11. [ReportExportService](#reportexportservice)
12. [CashAccountService](#cashaccountservice)
13. [PaymentAllocationService](#paymentallocationservice)
14. [Best Practices](#best-practices)

---

## Service Layer Overview

### Purpose

Services encapsulate **business logic** and keep controllers/Livewire components thin. They provide:

- **Reusability** - Logic used across multiple components
- **Testability** - Easy to unit test
- **Maintainability** - Centralized business rules
- **Transaction Safety** - Database transaction management

### Service Location

All services are located in: `app/Services/`

### Dependency Injection

Services are instantiated via Laravel's Service Container:

```php
use App\Services\InventoryService;

// In controller/Livewire component
$inventoryService = app(InventoryService::class);

// Or via constructor injection
public function __construct(
    protected InventoryService $inventoryService
) {}
```

---

## InventoryService

**Location:** `app/Services/InventoryService.php`

**Purpose:** Manage all stock operations with FIFO batch tracking

### Methods

#### `addStock(Product $product, float $quantity, array $details = []): StockMovement`

Add stock to inventory (creates an IN movement = new batch).

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Quantity to add (positive number)
- `$details` - Array of additional details:
  - `reference_type` - Reference model type (e.g., 'grn', 'adjustment')
  - `reference_id` - Reference model ID
  - `supplier_id` - Supplier ID (denormalized)
  - `supplier_name` - Supplier name (denormalized for fast lookup)
  - `batch_number` - Batch identifier
  - `expiry_date` - Product expiry date
  - `manufacturing_date` - Manufacturing date
  - `unit_cost` - Cost per unit
  - `min_selling_price` - Minimum selling price for this batch
  - `max_selling_price` - Maximum selling price (MRP) for this batch
  - `notes` - Optional notes

**Returns:** Created StockMovement instance

**Example:**
```php
$movement = $inventoryService->addStock($product, 100, [
    'reference_type' => 'grn',
    'reference_id' => $grn->id,
    'supplier_id' => $supplier->id,
    'supplier_name' => $supplier->name,  // Denormalized!
    'batch_number' => 'BATCH-2025-001',
    'expiry_date' => '2026-12-31',
    'manufacturing_date' => '2025-01-15',
    'unit_cost' => 50.00,
    'min_selling_price' => 60.00,
    'max_selling_price' => 75.00,
    'notes' => 'Received via GRN-001',
]);

// Product stock automatically increased
// Stock movement record created
// Batch tracking initiated
```

**Transactions:** Automatically wrapped in database transaction

---

#### `reduceStock(Product $product, float $quantity, array $details = []): StockMovement`

Reduce stock from inventory (creates an OUT movement with FIFO tracking).

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Quantity to reduce (positive number)
- `$details` - Array of additional details:
  - `reference_type` - Reference model type (e.g., 'sale')
  - `reference_id` - Reference model ID
  - `source_stock_movement_id` - Optional: specific batch to use (defaults to FIFO)
  - `notes` - Optional notes

**Returns:** Created StockMovement instance

**Example:**
```php
// Auto FIFO selection
$movement = $inventoryService->reduceStock($product, 10, [
    'reference_type' => 'sale',
    'reference_id' => $sale->id,
    'notes' => 'Sold to customer',
]);

// Manual batch selection
$fifoBatch = $inventoryService->getFIFOBatch($product);
$movement = $inventoryService->reduceStock($product, 10, [
    'reference_type' => 'sale',
    'reference_id' => $sale->id,
    'source_stock_movement_id' => $fifoBatch['stock_movement_id'],
    'notes' => 'Sold from specific batch',
]);
```

**Validation:**
- Checks if sufficient stock available
- Throws exception if insufficient stock
- Uses FIFO batch if source not specified

---

#### `getFIFOBatch(Product $product): ?array`

Get the oldest available batch (FIFO - First In, First Out).

**Parameters:**
- `$product` - Product model instance

**Returns:** Array with batch details or null if no batches

**Return Structure:**
```php
[
    'stock_movement_id' => 123,
    'batch_number' => 'BATCH-001',
    'unit_cost' => 50.00,
    'min_selling_price' => 60.00,
    'max_selling_price' => 75.00,
    'supplier_name' => 'ABC Suppliers',  // Zero joins!
    'expiry_date' => '2026-12-31',
]
```

**Example:**
```php
$batch = $inventoryService->getFIFOBatch($product);

if ($batch) {
    echo "Oldest Batch: {$batch['batch_number']}";
    echo "Supplier: {$batch['supplier_name']}";
    echo "Cost: {$batch['unit_cost']}";
    echo "Sell between: {$batch['min_selling_price']} - {$batch['max_selling_price']}";
}
```

**Performance:** Uses denormalized supplier_name - NO JOINS required!

---

#### `getAvailableBatches(Product $product): array`

Get all available batches for a product (FIFO ordered).

**Parameters:**
- `$product` - Product model instance

**Returns:** Array of batch details (oldest first)

**Return Structure:**
```php
[
    [
        'stock_movement_id' => 101,
        'batch_number' => 'BATCH-001',
        'grn_date' => '2025-01-15',
        'quantity_in' => 100,
        'unit_cost' => 50.00,
        'min_selling_price' => 60.00,
        'max_selling_price' => 75.00,
        'manufacturing_date' => '2025-01-10',
        'expiry_date' => '2026-12-31',
        'supplier_name' => 'ABC Suppliers',
    ],
    [
        'stock_movement_id' => 105,
        'batch_number' => 'BATCH-002',
        'grn_date' => '2025-01-20',
        'quantity_in' => 200,
        'unit_cost' => 52.00,
        'min_selling_price' => 62.00,
        'max_selling_price' => 77.00,
        'manufacturing_date' => '2025-01-15',
        'expiry_date' => '2027-01-15',
        'supplier_name' => 'XYZ Suppliers',
    ],
]
```

**Example:**
```php
$batches = $inventoryService->getAvailableBatches($product);

foreach ($batches as $batch) {
    echo "Batch: {$batch['batch_number']}";
    echo "Supplier: {$batch['supplier_name']}";  // No joins!
    echo "Qty: {$batch['quantity_in']}";
    echo "Expires: {$batch['expiry_date']}";
}
```

---

#### `getBatchDetails(int $stockMovementId): ?array`

Get details for a specific batch.

**Parameters:**
- `$stockMovementId` - Stock movement ID (batch ID)

**Returns:** Batch details array or null

**Example:**
```php
$details = $inventoryService->getBatchDetails($batchId);
```

---

#### `markAsDamaged(Product $product, float $quantity, string $notes = ''): StockMovement`

Mark stock as damaged (moves from good stock to damaged stock).

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Quantity damaged
- `$notes` - Reason for damage

**Returns:** Created StockMovement instance

**Example:**
```php
$movement = $inventoryService->markAsDamaged($product, 5, 'Damaged during transport');

// Creates OUT movement (reduces good stock)
// Increases damaged_stock_quantity
```

---

#### `writeOffDamaged(Product $product, float $quantity, string $notes = ''): StockMovement`

Write off damaged stock (permanent removal).

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Quantity to write off
- `$notes` - Reason for write-off

**Returns:** Created StockMovement instance

**Example:**
```php
$movement = $inventoryService->writeOffDamaged($product, 3, 'Expired products');

// Reduces damaged_stock_quantity
// Creates damage movement record
```

---

## POSService

**Location:** `app/Services/POSService.php`

**Purpose:** POS-specific calculations (pricing, discounts, stock validation)

### Methods

#### `calculateItemPrice(Product $product, float $quantity, bool $isBoxSale): array`

Calculate item price with box discount applied.

**Parameters:**
- `$product` - Product model instance with packaging relation
- `$quantity` - Quantity being purchased
- `$isBoxSale` - Whether this is a box sale

**Returns:** Array with pricing breakdown

**Return Structure:**
```php
[
    'unit_price' => 75.00,        // Max selling price (MRP)
    'base_total' => 1800.00,      // unit_price × quantity
    'discount' => 90.00,          // Box discount (if applicable)
    'final_total' => 1710.00,     // base_total - discount
]
```

**Example:**
```php
// Single bottle sale
$pricing = $posService->calculateItemPrice($product, 1, false);
// unit_price: 75, total: 75, discount: 0

// Box sale (24 bottles, 5% box discount)
$pricing = $posService->calculateItemPrice($product, 24, true);
// unit_price: 75, base_total: 1800, discount: 90, final_total: 1710
```

**Box Discount Logic:**
- Only applies if `$isBoxSale` is true
- Reads discount from `product_packaging` table
- Supports 'percentage' or 'fixed' discount types

---

#### `checkStock(Product $product, float $quantity): bool`

Check if product has sufficient stock.

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Required quantity

**Returns:** True if stock available, false otherwise

**Example:**
```php
if ($posService->checkStock($product, 10)) {
    // Process sale
} else {
    // Show "Out of stock" message
}
```

---

#### `validatePriceOverride(Product $product, float $newPrice): bool`

Validate if price override is within allowed range.

**Parameters:**
- `$product` - Product model instance
- `$newPrice` - New price to validate

**Returns:** True if valid, false if outside min/max range

**Example:**
```php
// Product: min=60, max=75
$isValid = $posService->validatePriceOverride($product, 70);  // true
$isValid = $posService->validatePriceOverride($product, 55);  // false (below min)
$isValid = $posService->validatePriceOverride($product, 80);  // false (above max)
```

---

#### `validateDiscountAuthorization(User $user, float $discountPercent): bool`

Check if user has authority to apply discount.

**Parameters:**
- `$user` - User model instance
- `$discountPercent` - Discount percentage to validate

**Returns:** True if authorized, false otherwise

**Example:**
```php
// Cashier (max 5%)
$canApply = $posService->validateDiscountAuthorization($cashier, 3);   // true
$canApply = $posService->validateDiscountAuthorization($cashier, 10);  // false

// Manager (max 20%)
$canApply = $posService->validateDiscountAuthorization($manager, 15);  // true

// Admin (no limit)
$canApply = $posService->validateDiscountAuthorization($admin, 50);    // true
```

**Authorization Levels:**
- Cashier: 5%
- Manager: 20%
- Admin: 100%

---

#### `getMaxDiscountForRole(string $role): int`

Get maximum discount allowed for a role.

**Parameters:**
- `$role` - User role ('cashier', 'manager', 'admin')

**Returns:** Maximum discount percentage

**Example:**
```php
$max = $posService->getMaxDiscountForRole('cashier');  // 5
$max = $posService->getMaxDiscountForRole('manager');  // 20
$max = $posService->getMaxDiscountForRole('admin');    // 100
```

---

## OfferService

**Location:** `app/Services/OfferService.php`

**Purpose:** Calculate and apply promotional offers

### Methods

#### `findBestOffer(Product $product, float $quantity, float $baseTotal): ?array`

Find the best applicable offer for a product.

**Parameters:**
- `$product` - Product model instance
- `$quantity` - Quantity being purchased
- `$baseTotal` - Base total before offers

**Returns:** Best offer details or null

**Return Structure:**
```php
[
    'offer_id' => 5,
    'discount_amount' => 50.00,
    'description' => 'Buy 10, Get 10% Off',
]
```

**Example:**
```php
$offer = $offerService->findBestOffer($product, 10, 750);

if ($offer) {
    echo "Offer Applied: {$offer['description']}";
    echo "Discount: Rs. {$offer['discount_amount']}";
}
```

**Offer Types Supported:**
- Percentage discount
- Fixed amount discount
- Buy X Get Y Free
- Minimum quantity requirements

---

#### `getActiveOffers(Product $product): Collection`

Get all active offers for a product.

**Parameters:**
- `$product` - Product model instance

**Returns:** Collection of Offer models

**Example:**
```php
$offers = $offerService->getActiveOffers($product);

foreach ($offers as $offer) {
    echo "{$offer->name}: {$offer->description}";
}
```

---

## LoyaltyService

**Location:** `app/Services/LoyaltyService.php`

**Purpose:** Customer loyalty points management

### Methods

#### `calculatePoints(float $amount): float`

Calculate points to be earned for a sale amount.

**Parameters:**
- `$amount` - Sale amount

**Returns:** Points to award

**Example:**
```php
// Settings: 1 point per Rs. 100
$points = $loyaltyService->calculatePoints(1500);  // 15 points
```

**Configuration:** Points ratio from Settings table

---

#### `awardPoints(Customer $customer, float $points, Sale $sale): PointTransaction`

Award points to customer.

**Parameters:**
- `$customer` - Customer model instance
- `$points` - Points to award
- `$sale` - Related sale

**Returns:** Created PointTransaction instance

**Example:**
```php
$transaction = $loyaltyService->awardPoints($customer, 15, $sale);

// Updates customer points_balance
// Creates point transaction record
// Links to sale for audit trail
```

---

#### `redeemPoints(Customer $customer, float $points, Sale $sale): PointTransaction`

Redeem customer points.

**Parameters:**
- `$customer` - Customer model instance
- `$points` - Points to redeem
- `$sale` - Related sale

**Returns:** Created PointTransaction instance

**Example:**
```php
$transaction = $loyaltyService->redeemPoints($customer, 50, $sale);

// Reduces customer points_balance
// Creates negative point transaction
// Applies discount to sale
```

**Validation:**
- Checks if customer has sufficient points
- Throws exception if insufficient

---

## TransactionService

**Location:** `app/Services/TransactionService.php`

**Purpose:** Double-entry accounting journal entries

### Methods

#### `postSale(Sale $sale): JournalEntry`

Post sale transaction to accounting.

**Parameters:**
- `$sale` - Sale model instance with items

**Returns:** Created JournalEntry instance

**Journal Entries Created:**
```
Dr: Cash/Bank Account          (Total amount)
Cr: Sales Revenue               (Total amount)

Dr: Cost of Goods Sold         (Total cost)
Cr: Inventory                   (Total cost)
```

**Example:**
```php
$journalEntry = $transactionService->postSale($sale);

// Automatically creates double-entry
// Updates account balances
// Links to sale for traceability
```

---

#### `postPurchase(GRN $grn): JournalEntry`

Post purchase (GRN) transaction to accounting.

**Parameters:**
- `$grn` - GRN model instance with items

**Returns:** Created JournalEntry instance

**Journal Entries Created:**
```
Dr: Inventory                   (Total amount)
Cr: Accounts Payable            (Total amount)
```

**Example:**
```php
$journalEntry = $transactionService->postPurchase($grn);

// Increases inventory account
// Increases accounts payable
// Updates supplier outstanding
```

---

#### `postPayment($payment): JournalEntry`

Post payment transaction to accounting.

**Parameters:**
- `$payment` - Payment model instance (Sale/Supplier payment)

**Returns:** Created JournalEntry instance

**Example:**
```php
$journalEntry = $transactionService->postPayment($salePayment);

// For sale payment:
// Dr: Bank Account
// Cr: Accounts Receivable
```

---

#### `isPosted(string $modelType, int $modelId): bool`

Check if transaction already posted to accounting.

**Parameters:**
- `$modelType` - Model class name
- `$modelId` - Model ID

**Returns:** True if posted, false otherwise

**Example:**
```php
if (!$transactionService->isPosted(Sale::class, $sale->id)) {
    $transactionService->postSale($sale);
}
```

**Purpose:** Prevents duplicate posting

---

## ReturnService

**Location:** `app/Services/ReturnService.php`

**Purpose:** Handle product returns and refunds

### Methods

#### `processReturn(array $data): SaleReturn`

Process a product return.

**Parameters:**
- `$data` - Array with return details:
  - `original_sale_id` - Original sale ID
  - `items` - Array of items being returned
  - `refund_method` - Refund method (cash/bank)
  - `notes` - Optional notes

**Returns:** Created SaleReturn instance

**Example:**
```php
$return = $returnService->processReturn([
    'original_sale_id' => $sale->id,
    'items' => [
        [
            'sale_item_id' => $saleItem->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 75.00,
            'reason' => 'Damaged product',
        ],
    ],
    'refund_method' => 'cash',
    'notes' => 'Customer complaint - damaged goods',
]);

// Creates SaleReturn record
// Creates SaleReturnItem records
// Adds stock back to inventory
// Updates customer points (deducts earned points)
// Creates refund transaction
```

**Transactions:** Wrapped in database transaction

---

## ShiftService

**Location:** `app/Services/ShiftService.php`

**Purpose:** Manage cashier shift operations

### Methods

#### `openShift(User $user, float $openingCash): Shift`

Open a new cashier shift.

**Parameters:**
- `$user` - User opening the shift
- `openingCash` - Opening cash amount

**Returns:** Created Shift instance

**Example:**
```php
$shift = $shiftService->openShift($user, 5000.00);

// Creates shift record
// Validates no other open shifts for user
// Sets status to 'open'
```

**Validation:**
- Only one open shift per user allowed
- Opening cash must be positive

---

#### `closeShift(Shift $shift, array $closingData): Shift`

Close a cashier shift.

**Parameters:**
- `$shift` - Shift to close
- `$closingData` - Array with:
  - `closing_cash` - Actual cash counted
  - `bank_amount` - Bank/card sales
  - `notes` - Optional notes

**Returns:** Updated Shift instance

**Example:**
```php
$shift = $shiftService->closeShift($shift, [
    'closing_cash' => 15750.00,
    'bank_amount' => 8500.00,
    'notes' => 'All transactions balanced',
]);

// Calculates variance
// Updates shift totals
// Sets status to 'closed'
```

**Calculations:**
- Expected Cash = Opening Cash + Cash Sales
- Variance = Closing Cash - Expected Cash

---

## BarcodeService

**Location:** `app/Services/BarcodeService.php`

**Purpose:** Barcode generation and validation

### Methods

#### `generateProductBarcode(): string`

Generate unique EAN-13 barcode for product.

**Returns:** 13-digit barcode string

**Example:**
```php
$barcode = $barcodeService->generateProductBarcode();
// Returns: "2012345678901"
```

**Format:** Starts with "20" + 11 random digits

---

#### `generatePackageBarcode(): string`

Generate unique barcode for package/box.

**Returns:** 13-digit barcode string

**Example:**
```php
$barcode = $barcodeService->generatePackageBarcode();
```

---

#### `validateBarcode(string $barcode): bool`

Validate barcode format.

**Parameters:**
- `$barcode` - Barcode to validate

**Returns:** True if valid EAN-13 format

**Example:**
```php
if ($barcodeService->validateBarcode($input)) {
    // Process barcode
}
```

---

## PrintService

**Location:** `app/Services/PrintService.php`

**Purpose:** Generate printable documents (receipts, invoices, reports)

### Methods

#### `generateReceipt(Sale $sale): string`

Generate HTML receipt for printing.

**Parameters:**
- `$sale` - Sale model instance with relations

**Returns:** HTML string

**Example:**
```php
$html = $printService->generateReceipt($sale);

// Returns formatted HTML receipt
// Includes logo, items, totals, payments
```

---

#### `generateInvoice(Sale $sale): string`

Generate detailed invoice (different from receipt).

**Returns:** HTML string for invoice

---

## ReportExportService

**Location:** `app/Services/ReportExportService.php`

**Purpose:** Export reports to PDF and Excel

### Methods

#### `exportToPDF(string $view, array $data, string $filename): Response`

Export data to PDF.

**Parameters:**
- `$view` - Blade view name
- `$data` - Data to pass to view
- `$filename` - Output filename

**Returns:** PDF download response

**Example:**
```php
return $exportService->exportToPDF(
    'reports.sales',
    ['sales' => $sales, 'total' => $total],
    'sales-report.pdf'
);
```

---

#### `exportToExcel(Collection $data, array $columns, string $filename): Response`

Export data to Excel.

**Parameters:**
- `$data` - Collection of data
- `$columns` - Column headers
- `$filename` - Output filename

**Returns:** Excel download response

**Example:**
```php
return $exportService->exportToExcel(
    $sales,
    ['Invoice', 'Date', 'Customer', 'Amount'],
    'sales-report.xlsx'
);
```

---

## CashAccountService

**Location:** `app/Services/CashAccountService.php`

**Purpose:** Manage cash transactions and balances

### Methods

#### `recordCashSale(Sale $sale): void`

Record cash sale transaction.

---

#### `recordBankSale(Sale $sale, Account $bankAccount): void`

Record bank/card sale transaction.

---

## PaymentAllocationService

**Location:** `app/Services/PaymentAllocationService.php`

**Purpose:** Allocate payments to invoices

### Methods

#### `allocatePayment(SupplierPayment $payment, array $allocations): void`

Allocate supplier payment to GRNs.

**Parameters:**
- `$payment` - SupplierPayment instance
- `$allocations` - Array of GRN allocations

**Example:**
```php
$allocations = [
    ['grn_id' => 1, 'amount' => 5000],
    ['grn_id' => 2, 'amount' => 3000],
];

$service->allocatePayment($payment, $allocations);

// Creates GRNPayment records
// Updates GRN payment status
// Reduces supplier outstanding balance
```

---

## Best Practices

### 1. Always Use Services for Business Logic

❌ **DON'T** put business logic in controllers/components:
```php
// Controller
public function store(Request $request)
{
    $product->increment('current_stock_quantity', $request->quantity);
    StockMovement::create([...]);
}
```

✅ **DO** use services:
```php
public function store(Request $request)
{
    $inventoryService = app(InventoryService::class);
    $inventoryService->addStock($product, $request->quantity, [...]);
}
```

### 2. Use Transactions for Multi-Step Operations

Services internally use transactions. Trust them:

```php
// Service handles transaction internally
$inventoryService->addStock($product, $quantity, $details);
```

### 3. Type Hint Service Dependencies

```php
use App\Services\InventoryService;
use App\Services\POSService;

public function __construct(
    protected InventoryService $inventoryService,
    protected POSService $posService
) {}
```

### 4. Return Structured Data

Services should return models or structured arrays:

```php
// ✅ Good
return [
    'unit_price' => 75.00,
    'discount' => 50.00,
    'final_total' => 700.00,
];

// ❌ Bad
return $price . '|' . $discount . '|' . $total;
```

### 5. Validate in Services

```php
public function addStock(Product $product, float $quantity, array $details): StockMovement
{
    if ($quantity <= 0) {
        throw new \InvalidArgumentException('Quantity must be positive');
    }

    // Process...
}
```

### 6. Document Service Methods

Use PHPDoc for all public methods:

```php
/**
 * Add stock to inventory with FIFO batch tracking
 *
 * @param Product $product Product to add stock to
 * @param float $quantity Quantity to add
 * @param array $details Additional batch details
 * @return StockMovement Created stock movement record
 * @throws \InvalidArgumentException If quantity is invalid
 */
public function addStock(Product $product, float $quantity, array $details): StockMovement
```

---

## Service Interaction Example

Real-world example of services working together:

```php
// Processing a sale in POS
public function completeSale()
{
    DB::transaction(function () {
        // 1. Use InventoryService to reduce stock
        foreach ($this->cartItems as $item) {
            $this->inventoryService->reduceStock(
                Product::find($item['product_id']),
                $item['quantity'],
                ['reference_type' => 'sale', 'reference_id' => $sale->id]
            );
        }

        // 2. Use LoyaltyService to award points
        if ($customer) {
            $points = $this->loyaltyService->calculatePoints($sale->total_amount);
            $this->loyaltyService->awardPoints($customer, $points, $sale);
        }

        // 3. Use TransactionService for accounting
        $this->transactionService->postSale($sale);

        // 4. Use PrintService to generate receipt
        $receipt = $this->printService->generateReceipt($sale);
    });
}
```

**Benefits:**
- Clean separation of concerns
- Each service handles its domain
- Easy to test independently
- Reusable across application

---

## Conclusion

Services are the **backbone of business logic** in GroceryERP. They provide:

✅ **Reusability** - Use across controllers, Livewire, CLI
✅ **Testability** - Unit test business logic
✅ **Maintainability** - Single source of truth
✅ **Transaction Safety** - Automatic rollback on errors
✅ **Performance** - Optimized queries (denormalized data)

**Next Steps:**
- Review **DEVELOPER_GUIDE.md** for implementation details
- Check model relationships for service integration
- Review existing Livewire components to see services in action

---

**Happy Coding! 🚀**
