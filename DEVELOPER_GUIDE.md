# GroceryERP - Developer Technical Guide

> Comprehensive technical documentation for developers working with the GroceryERP system.

**Version:** 1.0
**Last Updated:** 2025-11-22
**Tech Stack:** Laravel 11, Livewire 3, MySQL, Tailwind CSS

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Structure](#database-structure)
3. [Core Models Reference](#core-models-reference)
4. [Service Layer](#service-layer)
5. [Common Development Tasks](#common-development-tasks)
6. [Batch & Inventory Management](#batch--inventory-management)
7. [Payment & Credit Tracking](#payment--credit-tracking)
8. [Customer & Supplier Activities](#customer--supplier-activities)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Overview

GroceryERP follows a **Service-Repository pattern** with the following layers:

```
┌─────────────────────────────────────────┐
│         Livewire Components             │  ← UI Layer
├─────────────────────────────────────────┤
│         Service Layer                   │  ← Business Logic
├─────────────────────────────────────────┤
│         Eloquent Models                 │  ← Data Layer
├─────────────────────────────────────────┤
│         MySQL Database                  │  ← Persistence
└─────────────────────────────────────────┘
```

### Key Architectural Patterns

1. **Denormalized Supplier Data**: Supplier information stored in `stock_movements` for zero-join performance in POS
2. **FIFO Inventory**: Batch tracking using `stock_movements` table (each IN movement = batch)
3. **Double-Entry Accounting**: All financial transactions use Journal Entries
4. **Activity Logging**: All models use `LogsActivity` trait for audit trails

---

## Database Structure

### Core Tables

#### Products & Inventory

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `products` | Product master data | `sku`, `barcode`, `name`, `current_stock_quantity`, `min_selling_price`, `max_selling_price` |
| `product_packaging` | Box/package configuration | `pieces_per_package`, `package_barcode`, `discount_type`, `discount_value` |
| `stock_movements` | All stock transactions (FIFO batches) | `movement_type`, `quantity`, `supplier_id`, `supplier_name`, `batch_number`, `unit_cost`, `min_selling_price`, `max_selling_price` |
| `stock_adjustments` | Manual stock corrections | `adjustment_type`, `quantity`, `reason` |
| `categories` | Product categories | `name`, `parent_id` |

#### Purchasing

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `suppliers` | Supplier master data | `name`, `outstanding_balance`, `credit_terms` |
| `grns` | Goods Received Notes | `grn_number`, `supplier_id`, `total_amount`, `paid_amount`, `payment_status` |
| `grn_items` | GRN line items | `product_id`, `ordered_quantity`, `received_pieces`, `unit_price`, `batch_number`, `expiry_date`, `min_selling_price`, `max_selling_price` |
| `grn_payments` | GRN payment allocations | `grn_id`, `supplier_payment_id`, `amount` |
| `supplier_payments` | Supplier payment transactions | `supplier_id`, `amount`, `payment_method` |

#### Sales & POS

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sales` | Sales transactions | `invoice_number`, `customer_id`, `shift_id`, `total_amount`, `payment_status` |
| `sale_items` | Sale line items | `product_id`, `quantity`, `unit_price`, `stock_movement_id`, `offer_id` |
| `sale_payments` | Sale payment records | `sale_id`, `payment_method`, `amount` |
| `sale_returns` | Return transactions | `original_sale_id`, `total_refund_amount` |
| `shifts` | Cashier shifts | `user_id`, `opening_cash`, `total_cash_sales`, `total_bank_sales` |

#### Customers

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `customers` | Customer master data | `customer_code`, `name`, `phone`, `points_balance`, `total_purchases` |
| `point_transactions` | Loyalty points ledger | `customer_id`, `sale_id`, `points`, `transaction_type` |

#### Accounting

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `accounts` | Chart of accounts | `account_code`, `name`, `type`, `balance` |
| `journal_entries` | Financial transactions | `entry_number`, `entry_date`, `reference_type`, `reference_id` |
| `journal_entry_lines` | Journal entry lines | `journal_entry_id`, `account_id`, `debit`, `credit` |

#### Offers & Promotions

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `offers` | Promotional offers | `name`, `type`, `discount_value`, `start_date`, `end_date` |
| `offer_products` | Products in offer | `offer_id`, `product_id`, `min_quantity` |

---

## Core Models Reference

### Product Model

**Location:** `app/Models/Product.php`

**Key Methods:**

```php
// Check stock availability
$product->hasStock(): bool

// Check if below reorder level
$product->isBelowReorderLevel(): bool

// Calculate weighted average cost
$product->getAverageUnitCost(): float

// Generate unique SKU/Barcode
Product::generateUniqueSku(): string
Product::generateUniqueBarcode(): string

// Relationships
$product->category()           // BelongsTo Category
$product->packaging()          // HasOne ProductPackaging
$product->stockMovements()     // HasMany StockMovement

// Scopes
Product::active()->get()
Product::search('keyword')->get()
Product::byCategory($id)->get()
```

### StockMovement Model

**Location:** `app/Models/StockMovement.php`

**Movement Types:**
- `in` - Stock received (creates a batch)
- `out` - Stock sold/issued
- `adjustment` - Manual correction
- `damage` - Damaged stock
- `return` - Customer return

**Key Fields:**
```php
'movement_type'             // Type of movement
'quantity'                  // Positive for IN, Negative for OUT
'supplier_id'               // Denormalized supplier FK
'supplier_name'             // Denormalized supplier name (for fast lookups)
'batch_number'              // Batch identifier
'unit_cost'                 // Cost per unit
'min_selling_price'         // Batch-specific min price
'max_selling_price'         // Batch-specific max price (MRP)
'source_stock_movement_id'  // FIFO tracking - which batch depleted
'expiry_date'               // Product expiry
'manufacturing_date'        // Manufacturing date
```

**Key Methods:**

```php
// Scopes
StockMovement::stockIn()->get()
StockMovement::stockOut()->get()
StockMovement::expiringSoon(30)->get()
StockMovement::forProduct($productId)->get()

// Relationships
$movement->product()           // BelongsTo Product
$movement->performedBy()       // BelongsTo User
$movement->supplier()          // BelongsTo Supplier
$movement->grn()               // BelongsTo GRN (if reference_type = 'grn')

// Helpers
$movement->getMovementTypeLabel(): string
$movement->getDirection(): string  // 'increase' or 'decrease'
$movement->isExpired(): bool
```

### Customer Model

**Location:** `app/Models/Customer.php`

**Key Methods:**

```php
// Relationships
$customer->sales()              // HasMany Sale
$customer->pointTransactions()  // HasMany PointTransaction

// Scopes
Customer::active()->get()

// Helpers
Customer::generateCustomerCode(): string  // Returns 'CUST-000001'
```

### Supplier Model

**Location:** `app/Models/Supplier.php`

**Key Methods:**

```php
// Update outstanding balance
$supplier->updateOutstanding($amount, 'add');
$supplier->updateOutstanding($amount, 'subtract');

// Get totals
$supplier->getTotalPurchases(): float
$supplier->getTotalPayments(): float

// Relationships
$supplier->grns()               // HasMany GRN
$supplier->payments()           // HasMany SupplierPayment

// Scopes
Supplier::active()->get()
Supplier::withOutstanding()->get()
Supplier::search('keyword')->get()

// Validation
$supplier->canDelete(): bool
```

### GRN Model

**Location:** `app/Models/GRN.php`

**Statuses:**
- `draft` - Editable, not affecting stock
- `approved` - Stock updated, payment created

**Key Methods:**

```php
// Approve GRN (updates stock + creates journal entry)
$grn->approve(User $user): void

// Generate GRN number
GRN::generateGRNNumber(): string  // Returns 'GRN-20251122-001'

// Payment tracking
$grn->getOutstandingAmount(): float
$grn->recordPayment($paymentId, $amount): GRNPayment
$grn->updatePaymentStatus(): void

// Relationships
$grn->supplier()                // BelongsTo Supplier
$grn->items()                   // HasMany GRNItem
$grn->payments()                // HasMany GRNPayment
$grn->creator()                 // BelongsTo User
$grn->approver()                // BelongsTo User

// Scopes
GRN::draft()->get()
GRN::approved()->get()
GRN::unpaid()->get()
GRN::withOutstanding()->get()
```

### Sale Model

**Location:** `app/Models/Sale.php`

**Key Methods:**

```php
// Generate invoice number
Sale::generateInvoiceNumber(): string  // Returns 'INV-20251122-0001'

// Calculated attributes
$sale->paid_amount              // Total payments received
$sale->due_amount               // Outstanding amount
$sale->returned_amount          // Total returned

// Checks
$sale->hasDueAmount(): bool
$sale->hasBeenReturned(): bool

// Relationships
$sale->customer()               // BelongsTo Customer
$sale->shift()                  // BelongsTo Shift
$sale->items()                  // HasMany SaleItem
$sale->payments()               // HasMany SalePayment
$sale->returns()                // HasMany SaleReturn
$sale->cashier()                // BelongsTo User
```

---

## Service Layer

### InventoryService

**Location:** `app/Services/InventoryService.php`

**Purpose:** Manage all stock operations with proper FIFO tracking

**Key Methods:**

```php
use App\Services\InventoryService;

$service = app(InventoryService::class);

// Add stock (creates stock IN movement = new batch)
$movement = $service->addStock($product, $quantity, [
    'reference_type' => 'grn',
    'reference_id' => $grnId,
    'supplier_id' => $supplierId,              // ✅ Denormalized
    'supplier_name' => $supplierName,          // ✅ Denormalized for fast lookup
    'batch_number' => 'BATCH-001',
    'expiry_date' => '2025-12-31',
    'manufacturing_date' => '2025-01-01',
    'unit_cost' => 50.00,
    'min_selling_price' => 60.00,
    'max_selling_price' => 75.00,
    'notes' => 'Received via GRN-001',
]);

// Remove stock (creates stock OUT movement with FIFO tracking)
$movement = $service->reduceStock($product, $quantity, [
    'reference_type' => 'sale',
    'reference_id' => $saleId,
    'source_stock_movement_id' => $batchId,    // Optional: specify batch
    'notes' => 'Sold to customer',
]);

// Mark as damaged
$movement = $service->markAsDamaged($product, $quantity, [
    'notes' => 'Damaged during transport',
]);

// Write-off damaged stock
$movement = $service->writeOffDamaged($product, $quantity, 'Expired');

// Get FIFO batch (oldest batch with pricing)
$batch = $service->getFIFOBatch($product);
// Returns: ['unit_cost', 'min_selling_price', 'max_selling_price', 'batch_number', 'stock_movement_id', 'supplier_name']

// Get all available batches for a product
$batches = $service->getAvailableBatches($product);
// Returns array of: ['stock_movement_id', 'batch_number', 'quantity_in', 'unit_cost', 'min_selling_price', 'max_selling_price', 'grn_date', 'expiry_date', 'supplier_name']

// Get specific batch details
$batchDetails = $service->getBatchDetails($stockMovementId);
// Returns: ['stock_movement_id', 'batch_number', 'unit_cost', 'min_selling_price', 'max_selling_price', 'grn_date', 'expiry_date', 'supplier_name']
```

**Important:** Supplier information is now **denormalized** in stock_movements for performance!

### POSService

**Location:** `app/Services/POSService.php`

**Purpose:** POS-specific calculations (pricing, discounts, stock checks)

**Key Methods:**

```php
use App\Services\POSService;

$service = app(POSService::class);

// Calculate item price with box discount
$pricing = $service->calculateItemPrice($product, $quantity, $isBoxSale);
// Returns: ['unit_price', 'base_total', 'discount', 'final_total']

// Check stock availability
$hasStock = $service->checkStock($product, $quantity);

// Validate price override
$isValid = $service->validatePriceOverride($product, $newPrice);

// Check discount authorization by role
$canApply = $service->validateDiscountAuthorization($user, $discountPercent);

// Get max discount for role
$maxDiscount = $service->getMaxDiscountForRole('cashier');  // Returns: 5
$maxDiscount = $service->getMaxDiscountForRole('manager');  // Returns: 20
$maxDiscount = $service->getMaxDiscountForRole('admin');    // Returns: 100
```

### OfferService

**Location:** `app/Services/OfferService.php`

**Purpose:** Calculate and apply promotional offers

**Key Methods:**

```php
use App\Services\OfferService;

$service = app(OfferService::class);

// Find best applicable offer for a product
$offer = $service->findBestOffer($product, $quantity, $baseTotal);
// Returns: ['offer_id', 'discount_amount', 'description'] or null

// Get active offers for product
$offers = $service->getActiveOffers($product);
```

### TransactionService

**Location:** `app/Services/TransactionService.php`

**Purpose:** Double-entry accounting journal entries

**Key Methods:**

```php
use App\Services\TransactionService;

$service = app(TransactionService::class);

// Post sale to accounting
$journalEntry = $service->postSale($sale);

// Post purchase (GRN) to accounting
$journalEntry = $service->postPurchase($grn);

// Post payment to accounting
$journalEntry = $service->postPayment($payment);

// Check if already posted
$isPosted = $service->isPosted(Sale::class, $saleId);
```

### LoyaltyService

**Location:** `app/Services/LoyaltyService.php`

**Purpose:** Customer loyalty points management

**Key Methods:**

```php
use App\Services\LoyaltyService;

$service = app(LoyaltyService::class);

// Calculate points for sale amount
$points = $service->calculatePoints($saleAmount);

// Award points to customer
$transaction = $service->awardPoints($customer, $points, $sale);

// Redeem points
$transaction = $service->redeemPoints($customer, $points, $sale);
```

---

## Common Development Tasks

### 1. How to Create a Product

```php
use App\Models\Product;
use App\Models\Category;

// Create basic product
$product = Product::create([
    'sku' => Product::generateUniqueSku(),              // Auto-generate
    'barcode' => Product::generateUniqueBarcode(),       // Auto-generate
    'name' => 'Coca Cola 500ml',
    'category_id' => $category->id,
    'brand' => 'Coca Cola',
    'base_unit' => 'Bottle',
    'min_selling_price' => 25.00,                        // Minimum selling price
    'max_selling_price' => 30.00,                        // MRP (Maximum Retail Price)
    'reorder_level' => 50,
    'reorder_quantity' => 100,
    'enable_low_stock_alert' => true,
    'is_active' => true,
    'has_packaging' => true,                             // Enable box sales
    'created_by' => auth()->id(),
]);

// Add packaging (box) configuration
if ($product->has_packaging) {
    $product->packaging()->create([
        'pieces_per_package' => 24,                      // 24 bottles per box
        'package_name' => 'Box',
        'package_barcode' => Product::generateUniqueBarcode(),
        'discount_type' => 'percentage',                 // or 'fixed'
        'discount_value' => 5,                           // 5% discount on box
    ]);
}
```

### 2. How to Define Custom Min/Max Prices for Batches

**Batch-specific pricing** is defined when receiving stock via GRN:

```php
use App\Models\GRN;
use App\Models\GRNItem;

// Create GRN
$grn = GRN::create([
    'grn_number' => GRN::generateGRNNumber(),
    'supplier_id' => $supplier->id,
    'grn_date' => now(),
    'status' => 'draft',
    'created_by' => auth()->id(),
]);

// Add GRN item with batch-specific pricing
$grnItem = $grn->items()->create([
    'product_id' => $product->id,
    'ordered_quantity' => 100,
    'received_pieces' => 100,
    'unit_price' => 45.00,                               // Cost price
    'batch_number' => 'BATCH-2025-001',
    'expiry_date' => '2026-12-31',
    'manufacturing_date' => '2025-01-15',
    'min_selling_price' => 55.00,                        // ✅ Batch-specific minimum
    'max_selling_price' => 70.00,                        // ✅ Batch-specific MRP
]);

// Approve GRN to update stock
$grn->approve(auth()->user());

// This creates a stock_movement record with:
// - supplier_id (denormalized)
// - supplier_name (denormalized for fast POS lookups)
// - unit_cost = 45.00
// - min_selling_price = 55.00
// - max_selling_price = 70.00
// - batch_number = 'BATCH-2025-001'
```

**When selling in POS**, the system uses batch-specific prices from the FIFO batch!

### 3. How to Get Current Batch Stock

```php
use App\Services\InventoryService;
use App\Models\Product;

$inventoryService = app(InventoryService::class);
$product = Product::find($productId);

// Method 1: Get FIFO batch (oldest batch - auto-selected in POS)
$fifoBatch = $inventoryService->getFIFOBatch($product);

echo "Batch: {$fifoBatch['batch_number']}";
echo "Supplier: {$fifoBatch['supplier_name']}";          // ✅ Zero joins!
echo "Cost: {$fifoBatch['unit_cost']}";
echo "Min Price: {$fifoBatch['min_selling_price']}";
echo "Max Price: {$fifoBatch['max_selling_price']}";
echo "Batch ID: {$fifoBatch['stock_movement_id']}";

// Method 2: Get all available batches
$batches = $inventoryService->getAvailableBatches($product);

foreach ($batches as $batch) {
    echo "Batch: {$batch['batch_number']} | Supplier: {$batch['supplier_name']} | Qty: {$batch['quantity_in']}";
}

// Method 3: Get specific batch details
$batchDetails = $inventoryService->getBatchDetails($stockMovementId);

// Method 4: Query stock movements directly
$activeBatches = StockMovement::where('product_id', $product->id)
    ->where('movement_type', 'in')
    ->whereNotNull('unit_cost')
    ->orderBy('created_at', 'asc')  // FIFO order
    ->get();

foreach ($activeBatches as $batch) {
    echo "Supplier: {$batch->supplier_name}";  // ✅ Direct access, no joins!
    echo "Batch: {$batch->batch_number}";
    echo "Qty: {$batch->quantity}";
}
```

### 4. How to Find Supplier

```php
use App\Models\Supplier;

// Method 1: Search by keyword
$suppliers = Supplier::search('ABC')->active()->get();

// Method 2: Get suppliers with outstanding balance
$suppliersWithDues = Supplier::withOutstanding()->get();

foreach ($suppliersWithDues as $supplier) {
    echo "{$supplier->name}: Rs. {$supplier->outstanding_balance}";
}

// Method 3: Get supplier from stock movement (fast!)
$stockMovement = StockMovement::find($batchId);
echo "Supplier: {$stockMovement->supplier_name}";       // ✅ Denormalized, no join!

// Or use relationship if needed
$supplier = $stockMovement->supplier;                    // Uses supplier_id FK

// Method 4: Get supplier totals
$supplier = Supplier::find($id);
$totalPurchases = $supplier->getTotalPurchases();
$totalPayments = $supplier->getTotalPayments();
$outstanding = $supplier->outstanding_balance;
```

### 5. How to Track Customer Activities

```php
use App\Models\Customer;

$customer = Customer::find($customerId);

// Get all sales
$sales = $customer->sales()->latest()->get();

foreach ($sales as $sale) {
    echo "Invoice: {$sale->invoice_number}";
    echo "Total: Rs. {$sale->total_amount}";
    echo "Date: {$sale->sale_date->format('Y-m-d')}";
}

// Get total purchases
$totalPurchases = $customer->total_purchases;

// Get loyalty points
$pointsBalance = $customer->points_balance;

// Get point transaction history
$pointHistory = $customer->pointTransactions()
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($pointHistory as $transaction) {
    echo "{$transaction->transaction_type}: {$transaction->points} points";
}

// Get sales within date range
$monthlySales = $customer->sales()
    ->whereBetween('sale_date', [
        now()->startOfMonth(),
        now()->endOfMonth()
    ])
    ->get();

// Get unpaid sales (credit sales)
$creditSales = $customer->sales()
    ->where('payment_status', '!=', 'paid')
    ->get();
```

### 6. How to Track Supplier Activities

```php
use App\Models\Supplier;

$supplier = Supplier::find($supplierId);

// Get all GRNs (purchases)
$grns = $supplier->grns()
    ->with('items.product')
    ->latest()
    ->get();

foreach ($grns as $grn) {
    echo "GRN: {$grn->grn_number}";
    echo "Amount: Rs. {$grn->total_amount}";
    echo "Paid: Rs. {$grn->paid_amount}";
    echo "Status: {$grn->payment_status}";
}

// Get all payments
$payments = $supplier->payments()
    ->orderBy('payment_date', 'desc')
    ->get();

foreach ($payments as $payment) {
    echo "Date: {$payment->payment_date->format('Y-m-d')}";
    echo "Amount: Rs. {$payment->amount}";
    echo "Method: {$payment->payment_method}";
}

// Get totals
$totalPurchases = $supplier->getTotalPurchases();      // Sum of approved GRNs
$totalPayments = $supplier->getTotalPayments();        // Sum of payments
$outstanding = $supplier->outstanding_balance;         // Current dues

// Get unpaid GRNs
$unpaidGrns = $supplier->grns()
    ->unpaid()
    ->get();

// Get partially paid GRNs
$partiallyPaid = $supplier->grns()
    ->partiallyPaid()
    ->get();

// Get GRNs with outstanding balance
$dueGrns = $supplier->grns()
    ->withOutstanding()
    ->get();
```

### 7. How to Know Pending Payments (Customer Credits)

```php
use App\Models\Sale;
use App\Models\Customer;

// Method 1: Get all unpaid/partially paid sales
$pendingSales = Sale::whereIn('payment_status', ['unpaid', 'partially_paid'])
    ->with('customer')
    ->latest()
    ->get();

foreach ($pendingSales as $sale) {
    echo "Invoice: {$sale->invoice_number}";
    echo "Customer: {$sale->customer->name}";
    echo "Total: Rs. {$sale->total_amount}";
    echo "Paid: Rs. {$sale->paid_amount}";
    echo "Due: Rs. {$sale->due_amount}";
}

// Method 2: Get pending payments for specific customer
$customer = Customer::find($customerId);
$customerCredits = $customer->sales()
    ->where('payment_status', '!=', 'paid')
    ->get();

$totalDue = $customerCredits->sum('due_amount');

// Method 3: Get detailed payment breakdown
$sale = Sale::with('payments')->find($saleId);

echo "Total Amount: Rs. {$sale->total_amount}";
echo "Payments Made:";

foreach ($sale->payments as $payment) {
    echo "  - {$payment->payment_method}: Rs. {$payment->amount} on {$payment->created_at->format('Y-m-d')}";
}

echo "Outstanding: Rs. {$sale->due_amount}";

// Method 4: Get aging report (dues by time period)
$overdueBy30Days = Sale::where('payment_status', '!=', 'paid')
    ->where('sale_date', '<', now()->subDays(30))
    ->get();

$overdueBy60Days = Sale::where('payment_status', '!=', 'paid')
    ->where('sale_date', '<', now()->subDays(60))
    ->get();
```

### 8. How to Know Pending Credits (Supplier Payables)

```php
use App\Models\GRN;
use App\Models\Supplier;

// Method 1: Get all unpaid/partially paid GRNs
$pendingPayables = GRN::whereIn('payment_status', ['unpaid', 'partially_paid'])
    ->with('supplier')
    ->latest()
    ->get();

foreach ($pendingPayables as $grn) {
    echo "GRN: {$grn->grn_number}";
    echo "Supplier: {$grn->supplier->name}";
    echo "Total: Rs. {$grn->total_amount}";
    echo "Paid: Rs. {$grn->paid_amount}";
    echo "Outstanding: Rs. {$grn->getOutstandingAmount()}";
}

// Method 2: Get payables for specific supplier
$supplier = Supplier::find($supplierId);
$supplierPayables = $supplier->grns()
    ->withOutstanding()
    ->get();

$totalPayable = $supplier->outstanding_balance;

// Method 3: Get detailed payment breakdown
$grn = GRN::with('payments.supplierPayment')->find($grnId);

echo "Total Amount: Rs. {$grn->total_amount}";
echo "Payments Made:";

foreach ($grn->payments as $payment) {
    $supplierPayment = $payment->supplierPayment;
    echo "  - Rs. {$payment->amount} via {$supplierPayment->payment_method} on {$supplierPayment->payment_date->format('Y-m-d')}";
}

echo "Outstanding: Rs. {$grn->getOutstandingAmount()}";

// Method 4: Get all suppliers with outstanding balance
$suppliersWithDues = Supplier::withOutstanding()
    ->get();

foreach ($suppliersWithDues as $supplier) {
    echo "{$supplier->name}: Rs. {$supplier->outstanding_balance}";
}

// Method 5: Get aging report
$overdueBy30Days = GRN::withOutstanding()
    ->where('grn_date', '<', now()->subDays(30))
    ->get();
```

### 9. How to Process a Sale with FIFO

```php
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\InventoryService;

$inventoryService = app(InventoryService::class);

// Create sale
$sale = Sale::create([
    'invoice_number' => Sale::generateInvoiceNumber(),
    'customer_id' => $customerId,
    'shift_id' => $shiftId,
    'sale_date' => now(),
    'subtotal' => $subtotal,
    'discount_amount' => $discountAmount,
    'total_amount' => $totalAmount,
    'payment_status' => 'paid',
    'created_by' => auth()->id(),
]);

// Add sale items
foreach ($cartItems as $item) {
    // Get FIFO batch for this product
    $fifoBatch = $inventoryService->getFIFOBatch($product);

    // Create sale item
    $saleItem = $sale->items()->create([
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity'],
        'unit_price' => $item['unit_price'],              // Can be adjusted between min/max
        'discount_amount' => $item['discount'],
        'total_amount' => $item['total'],
        'stock_movement_id' => $fifoBatch['stock_movement_id'],  // Track batch
        'offer_id' => $item['offer_id'],
    ]);

    // Reduce stock (creates OUT movement linked to FIFO batch)
    $inventoryService->reduceStock($product, $item['quantity'], [
        'reference_type' => 'sale',
        'reference_id' => $sale->id,
        'source_stock_movement_id' => $fifoBatch['stock_movement_id'],
        'unit_cost' => $fifoBatch['unit_cost'],
        'min_selling_price' => $fifoBatch['min_selling_price'],
        'max_selling_price' => $fifoBatch['max_selling_price'],
        'batch_number' => $fifoBatch['batch_number'],
    ]);
}

// Record payment
$sale->payments()->create([
    'payment_method' => 'cash',
    'amount' => $totalAmount,
]);
```

### 10. How to Handle Product Returns

```php
use App\Models\SaleReturn;
use App\Services\ReturnService;

$returnService = app(ReturnService::class);

// Process return
$return = $returnService->processReturn([
    'original_sale_id' => $saleId,
    'items' => [
        [
            'sale_item_id' => $saleItemId,
            'product_id' => $productId,
            'quantity' => $returnQuantity,
            'unit_price' => $unitPrice,
            'reason' => 'Damaged product',
        ],
    ],
    'refund_method' => 'cash',
    'notes' => 'Customer complaint',
]);

// Return automatically:
// 1. Creates SaleReturn record
// 2. Creates SaleReturnItem records
// 3. Adds stock back (creates IN movement)
// 4. Updates customer points (if loyalty enabled)
// 5. Creates refund transaction
```

---

## Batch & Inventory Management

### Understanding FIFO Batch Tracking

**Key Concept:** Each `stock IN movement` represents a **batch**

```
┌─────────────────────────────────────┐
│ Stock Movement (movement_type='in') │
├─────────────────────────────────────┤
│ id: 101                             │
│ product_id: 5                       │
│ movement_type: 'in'                 │
│ quantity: 100                       │
│ supplier_id: 3                      │  ← Denormalized
│ supplier_name: 'ABC Suppliers'      │  ← Denormalized (fast!)
│ batch_number: 'BATCH-2025-001'      │
│ unit_cost: 45.00                    │
│ min_selling_price: 55.00            │  ← Batch-specific
│ max_selling_price: 70.00            │  ← Batch-specific
│ expiry_date: '2026-12-31'           │
│ created_at: '2025-01-15'            │
└─────────────────────────────────────┘
         This IS the batch!
```

### Batch Lifecycle

1. **Stock Received (IN movement created)**
   ```php
   $inventoryService->addStock($product, 100, [
       'supplier_id' => 3,
       'supplier_name' => 'ABC Suppliers',  // Denormalized!
       'batch_number' => 'BATCH-001',
       'min_selling_price' => 55,
       'max_selling_price' => 70,
   ]);
   ```

2. **Stock Sold (OUT movement created, linked to IN movement)**
   ```php
   $inventoryService->reduceStock($product, 10, [
       'source_stock_movement_id' => 101,   // Links to batch
   ]);
   ```

3. **Query Batches**
   ```php
   // Get all active batches for product
   $batches = StockMovement::where('product_id', $productId)
       ->where('movement_type', 'in')
       ->orderBy('created_at', 'asc')       // FIFO order
       ->get();

   foreach ($batches as $batch) {
       echo $batch->supplier_name;          // ✅ Zero joins!
   }
   ```

### Why Denormalized Supplier Data?

**Problem:** Original design required 2-hop joins:
```sql
SELECT sm.*, s.name
FROM stock_movements sm
JOIN grns g ON sm.reference_id = g.id
JOIN suppliers s ON g.supplier_id = s.id
WHERE sm.product_id = 5;
```

**Solution:** Store supplier info directly in stock_movements:
```sql
SELECT sm.supplier_name, sm.batch_number
FROM stock_movements sm
WHERE sm.product_id = 5;
```

**Performance:** POS with 50 products = **100 joins eliminated** = ⚡ instant!

---

## Payment & Credit Tracking

### Payment Status Flow

#### Sale Payments

```
Unpaid → Partially Paid → Paid
```

```php
// Check payment status
$sale = Sale::find($id);

if ($sale->payment_status === 'paid') {
    echo "Fully paid";
} elseif ($sale->payment_status === 'partially_paid') {
    echo "Partial payment: Rs. {$sale->paid_amount} of Rs. {$sale->total_amount}";
    echo "Due: Rs. {$sale->due_amount}";
} else {
    echo "Unpaid: Rs. {$sale->total_amount}";
}

// Record additional payment
$sale->payments()->create([
    'payment_method' => 'bank_transfer',
    'amount' => 500.00,
    'reference_number' => 'TXN-123456',
]);

// Payment status automatically updates via model events
```

#### GRN Payments

```php
$grn = GRN::find($id);

// Create supplier payment
$supplierPayment = $grn->supplier->payments()->create([
    'amount' => 10000.00,
    'payment_date' => now(),
    'payment_method' => 'cheque',
    'reference_number' => 'CHQ-789',
]);

// Allocate payment to specific GRNs
$grn->recordPayment($supplierPayment->id, 5000.00);

// GRN payment status updates automatically
// Supplier outstanding balance decreases automatically
```

### Double-Entry Accounting

All financial transactions create journal entries:

```php
use App\Services\TransactionService;

$transactionService = app(TransactionService::class);

// Sale transaction
$journalEntry = $transactionService->postSale($sale);

// Creates entries like:
// Debit: Cash/Bank Account
// Credit: Sales Revenue Account
// Debit: Cost of Goods Sold
// Credit: Inventory Account

// Purchase transaction
$journalEntry = $transactionService->postPurchase($grn);

// Creates entries like:
// Debit: Inventory Account
// Credit: Accounts Payable
```

---

## Customer & Supplier Activities

### Activity Logging

All models use `LogsActivity` trait for audit trails:

```php
use App\Models\ActivityLog;

// Get activity logs for specific model
$logs = ActivityLog::where('subject_type', Product::class)
    ->where('subject_id', $productId)
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($logs as $log) {
    echo "{$log->causer->name} {$log->description} at {$log->created_at}";
    print_r($log->properties);  // Contains old/new values
}

// Get all activities by user
$userActivities = ActivityLog::where('causer_id', $userId)
    ->latest()
    ->get();
```

### Customer Analytics

```php
use App\Models\Customer;
use App\Models\Sale;

$customer = Customer::find($id);

// Total lifetime value
$lifetimeValue = $customer->total_purchases;

// Average order value
$avgOrderValue = $customer->sales()->avg('total_amount');

// Purchase frequency
$totalOrders = $customer->sales()->count();
$firstPurchase = $customer->sales()->oldest()->first()->sale_date;
$daysSinceFirst = now()->diffInDays($firstPurchase);
$frequency = $totalOrders / max(1, $daysSinceFirst);

// Last purchase date
$lastPurchase = $customer->sales()->latest()->first();

// Top products purchased
$topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
    ->where('sales.customer_id', $customer->id)
    ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_qty'))
    ->groupBy('sale_items.product_id')
    ->orderBy('total_qty', 'desc')
    ->limit(10)
    ->get();
```

### Supplier Analytics

```php
use App\Models\Supplier;
use App\Models\GRN;

$supplier = Supplier::find($id);

// Total purchase value
$totalPurchases = $supplier->getTotalPurchases();

// Average GRN value
$avgGrnValue = $supplier->grns()->approved()->avg('total_amount');

// Purchase frequency
$grnCount = $supplier->grns()->approved()->count();

// Payment reliability (days to payment)
$grns = $supplier->grns()->approved()->with('payments')->get();
$avgDaysToPayment = $grns->map(function($grn) {
    $firstPayment = $grn->payments()->oldest()->first();
    return $firstPayment ? $grn->grn_date->diffInDays($firstPayment->created_at) : null;
})->filter()->avg();

// Outstanding balance
$outstanding = $supplier->outstanding_balance;

// Top products supplied
$topProducts = GRNItem::join('grns', 'grn_items.grn_id', '=', 'grns.id')
    ->where('grns.supplier_id', $supplier->id)
    ->where('grns.status', 'approved')
    ->select('grn_items.product_id', DB::raw('SUM(grn_items.received_pieces) as total_qty'))
    ->groupBy('grn_items.product_id')
    ->orderBy('total_qty', 'desc')
    ->limit(10)
    ->get();
```

---

## Best Practices

### 1. Always Use Services for Business Logic

❌ **DON'T:**
```php
// In controller or Livewire component
$product->increment('current_stock_quantity', 100);
StockMovement::create([...]);
```

✅ **DO:**
```php
$inventoryService = app(InventoryService::class);
$inventoryService->addStock($product, 100, [...]);
```

### 2. Use Transactions for Multi-Step Operations

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($sale, $items) {
    // Create sale
    $sale->save();

    // Create items
    foreach ($items as $item) {
        $sale->items()->create($item);
    }

    // Update stock
    foreach ($items as $item) {
        $inventoryService->reduceStock(...);
    }

    // All or nothing!
});
```

### 3. Leverage Denormalized Data

```php
// ✅ Use denormalized fields for performance
$supplierName = $stockMovement->supplier_name;  // Fast!

// ❌ Avoid unnecessary joins when denormalized data exists
$supplierName = $stockMovement->grn->supplier->name;  // Slow!
```

### 4. Use Eloquent Scopes

```php
// ✅ Reusable query logic
$activeProducts = Product::active()->search($keyword)->get();

// ❌ Repeating queries
$products = Product::where('is_active', true)
    ->where('name', 'like', "%{$keyword}%")
    ->get();
```

### 5. Validate Before Mutating State

```php
// ✅ Check before modifying
if (!$product->hasStock()) {
    throw new \Exception('Insufficient stock');
}

if (!$supplier->canDelete()) {
    throw new \Exception('Supplier has transactions');
}

// Then modify
$product->decrement('current_stock_quantity', 10);
```

### 6. Use Model Events Wisely

```php
// GRN Observer example
protected static function booted()
{
    static::updated(function ($grn) {
        if ($grn->isDirty('status') && $grn->status === 'approved') {
            // Auto-post to accounting
            app(TransactionService::class)->postPurchase($grn);
        }
    });
}
```

---

## Troubleshooting

### Common Issues

#### 1. Stock Mismatch

**Problem:** `current_stock_quantity` doesn't match actual stock movements

**Solution:**
```php
use App\Models\Product;
use App\Models\StockMovement;

$product = Product::find($id);

// Calculate actual stock from movements
$actualStock = StockMovement::where('product_id', $product->id)
    ->sum('quantity');  // IN = positive, OUT = negative

// Compare
if ($product->current_stock_quantity != $actualStock) {
    // Fix discrepancy
    $product->update(['current_stock_quantity' => $actualStock]);
}
```

#### 2. Supplier Name Not Showing in POS

**Problem:** Old stock movements don't have supplier_name

**Solution:** Run the backfill migration:
```bash
php artisan migrate
```

This populates `supplier_id` and `supplier_name` from GRN relationships.

#### 3. FIFO Batch Returns Wrong Data

**Problem:** `getFIFOBatch()` returns null or incomplete data

**Reason:** No stock IN movements with pricing data

**Solution:**
```php
// Check if stock IN movements exist
$hasInMovements = StockMovement::where('product_id', $productId)
    ->where('movement_type', 'in')
    ->whereNotNull('unit_cost')
    ->exists();

if (!$hasInMovements) {
    // Product has stock but no IN movements
    // This happens if stock was added before pricing was implemented
    // Need to create a retroactive IN movement
}
```

#### 4. Outstanding Balance Incorrect

**Problem:** Supplier/Customer balance doesn't match actual dues

**Solution:**
```php
// Recalculate supplier outstanding
$supplier = Supplier::find($id);

$totalGrns = $supplier->grns()->approved()->sum('total_amount');
$totalPayments = $supplier->payments()->sum('amount');
$correctOutstanding = $totalGrns - $totalPayments;

$supplier->update(['outstanding_balance' => $correctOutstanding]);
```

### Performance Optimization Tips

1. **Eager Load Relationships**
   ```php
   // ✅ Eager load
   $sales = Sale::with('customer', 'items.product')->get();

   // ❌ N+1 problem
   $sales = Sale::all();
   foreach ($sales as $sale) {
       echo $sale->customer->name;  // Query per sale!
   }
   ```

2. **Use Chunk for Large Datasets**
   ```php
   Product::chunk(100, function ($products) {
       foreach ($products as $product) {
           // Process
       }
   });
   ```

3. **Cache Frequently Accessed Data**
   ```php
   $categories = Cache::remember('categories', 3600, function () {
       return Category::active()->get();
   });
   ```

---

## API Reference Quick Guide

### Product APIs

```php
Product::find($id)
Product::active()->get()
Product::search($keyword)->get()
Product::byCategory($categoryId)->get()
$product->hasStock()
$product->isBelowReorderLevel()
$product->getAverageUnitCost()
```

### Inventory APIs

```php
$inventoryService->addStock($product, $qty, $details)
$inventoryService->reduceStock($product, $qty, $details)
$inventoryService->markAsDamaged($product, $qty, $reason)
$inventoryService->getFIFOBatch($product)
$inventoryService->getAvailableBatches($product)
$inventoryService->getBatchDetails($batchId)
```

### Sales APIs

```php
Sale::find($id)
Sale::generateInvoiceNumber()
$sale->paid_amount
$sale->due_amount
$sale->hasDueAmount()
```

### Supplier APIs

```php
Supplier::active()->get()
Supplier::withOutstanding()->get()
$supplier->updateOutstanding($amount, 'add|subtract')
$supplier->getTotalPurchases()
$supplier->getTotalPayments()
```

### GRN APIs

```php
GRN::generateGRNNumber()
$grn->approve($user)
$grn->getOutstandingAmount()
$grn->recordPayment($paymentId, $amount)
GRN::draft()->get()
GRN::approved()->get()
GRN::withOutstanding()->get()
```

---

## Conclusion

This guide covers the core technical aspects of GroceryERP. For additional information:

- **Activity Logs:** Check `activity_log` table
- **Settings:** See `settings` table for system configuration
- **Notifications:** See `notifications` table
- **Shifts:** See `shifts` table for cashier shift management

**Key Takeaways:**
1. Stock movements = Batches (FIFO)
2. Supplier data is denormalized for performance
3. Always use Services for business logic
4. All financial transactions create journal entries
5. Use scopes and relationships for clean queries

Happy coding! 🚀
