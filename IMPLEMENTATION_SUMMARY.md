# Implementation Summary: Core Accounting Setup & Stock Movements Tracking

## Overview
This implementation adds two major features to the Grocery ERP system:
1. **Core Accounting Setup** - Chart of Accounts management
2. **Stock Movements Tracking** - Inventory movement tracking system

---

## TASK 1: Core Accounting Setup ✅

### 1. Database Migration (accounts)
**File:** `database/migrations/2024_11_15_000004_create_accounts_table.php`

**Fields:**
- `id` - Primary key
- `account_code` - Unique, indexed (e.g., 'CASH', 'BANK1')
- `account_name` - Display name (e.g., 'Cash', 'Bank Account 1')
- `account_type` - Enum: asset, liability, income, expense, equity
- `parent_id` - Self-referential foreign key for hierarchy
- `is_system_account` - Boolean (protected accounts)
- `balance` - Decimal(15,2), default 0
- `is_active` - Boolean, default true
- `timestamps` - created_at, updated_at

### 2. Account Model
**File:** `app/Models/Account.php`

**Features:**
- Self-referential relationships (parent/children)
- LogsActivity trait applied
- Scopes: active(), byType(), system(), custom()
- Helper methods: hasChildren(), canBeDeleted(), canBeEdited()
- Protected system accounts from editing/deletion

### 3. Account Seeder
**File:** `database/seeders/AccountSeeder.php`

**Default System Accounts:**
- CASH - Cash (Asset)
- BANK1 - Bank Account 1 (Asset)
- BANK2 - Bank Account 2 (Asset)
- SALES - Sales Revenue (Income)
- PURCHASES - Purchases (Expense)
- INVENTORY - Inventory (Asset)

**Note:** Added to `DatabaseSeeder.php`

### 4. Account CRUD (Livewire Components)

**Components:**
- `app/Livewire/Accounts/AccountManagement.php` - Main list page
- `app/Livewire/Accounts/CreateAccount.php` - Create modal
- `app/Livewire/Accounts/EditAccount.php` - Edit modal

**Views:**
- `resources/views/livewire/accounts/account-management.blade.php`
- `resources/views/livewire/accounts/create-account.blade.php`
- `resources/views/livewire/accounts/edit-account.blade.php`

**Features:**
- Filterable by: search, type, status
- Account hierarchy display
- System accounts protected (cannot edit/delete)
- Custom accounts can be fully managed
- Balance display with color coding
- Parent/child account selection

### 5. Routes & Navigation
**Routes:** Added to `routes/web.php`
```php
Route::middleware(['check.role:accountant,admin'])->group(function () {
    Route::get('/accounts', AccountManagement::class)->name('accounts.index');
});
```

**Sidebar Menu:** Added to `resources/views/layouts/sidebar-navigation.blade.php`
- Visible to: admin, accountant roles
- Icon: Document/file icon

---

## TASK 2: Stock Movements Tracking ✅

### 1. Products Table (Prerequisite)
**File:** `database/migrations/2024_11_15_000004_create_products_table.php`

**Fields:**
- Basic info: name, sku, category_id, description
- Pricing: unit_price, box_price, pieces_per_box
- Stock: current_stock_quantity, damaged_stock_quantity, minimum_stock_level
- Additional: barcode, is_active
- All stock quantities in pieces (decimal 10,2)

### 2. Product Model
**File:** `app/Models/Product.php`

**Features:**
- LogsActivity trait applied
- Relationships: category, stockMovements
- Scopes: active(), lowStock()
- Helper methods: isLowStock(), hasBoxPricing(), getStockValue(), getAvailableStock()
- Updated Category model to include products relationship

### 3. Stock Movements Migration
**File:** `database/migrations/2024_11_15_000005_create_stock_movements_table.php`

**Fields:**
- `product_id` - Foreign key to products
- `movement_type` - Enum: in, out, adjustment, damage, return
- `quantity` - Decimal(10,2) - positive for IN, negative for OUT
- `reference_type` - Nullable enum: sale, grn, adjustment, return
- `reference_id` - Nullable bigint for related records
- `batch_number` - Nullable string
- `expiry_date` - Nullable date
- `notes` - Nullable text
- `performed_by` - Foreign key to users
- `created_at` - Timestamp (no updated_at - append only)

**Indexes:** product_id, movement_type, reference_type/id, performed_by, created_at

### 4. StockMovement Model
**File:** `app/Models/StockMovement.php`

**Features:**
- LogsActivity trait applied
- Relationships: product(), performedBy()
- Scopes: byType(), forProduct(), dateRange(), byReference()
- Helper methods: getMovementTypeLabel(), getDirection()
- Append-only (no timestamps update)

### 5. InventoryService
**File:** `app/Services/InventoryService.php`

**Methods:**

#### `addStock(Product $product, float $quantity, array $details)`
- Validates quantity > 0
- Increments product.current_stock_quantity
- Creates stock movement record (type: 'in')
- Uses DB transaction
- Returns StockMovement

#### `reduceStock(Product $product, float $quantity, array $details)`
- Validates quantity > 0
- Checks sufficient stock available
- Decrements product.current_stock_quantity
- Creates stock movement record (type: 'out', negative quantity)
- Uses DB transaction
- Throws exception if insufficient stock
- Returns StockMovement

#### `markAsDamaged(Product $product, float $quantity, ?string $reason)`
- Validates quantity > 0
- Checks sufficient stock available
- Decrements current_stock_quantity
- Increments damaged_stock_quantity
- Creates stock movement record (type: 'damage', negative quantity)
- Uses DB transaction
- Returns StockMovement

#### `adjustStock(Product $product, float $quantity, string $reason)`
- Validates quantity != 0
- Supports positive (add) or negative (reduce) adjustments
- Updates current_stock_quantity accordingly
- Creates stock movement record (type: 'adjustment')
- Uses DB transaction
- Returns StockMovement

#### Helper Methods
- `getStockHistory()` - Get movement history for a product
- `getTotalStockIn()` - Calculate total IN for date range
- `getTotalStockOut()` - Calculate total OUT for date range

**Important:** All stock changes MUST go through InventoryService (enforced in future phases)

### 6. Stock Movements Viewer (Livewire)
**Component:** `app/Livewire/Inventory/StockMovements.php`
**View:** `resources/views/livewire/inventory/stock-movements.blade.php`

**Features:**
- Read-only view (no editing/deleting)
- Filters:
  - Search by product name or SKU
  - Filter by product
  - Filter by movement type
  - Date range filter (start/end date)
- Display columns:
  - Date & Time
  - Product (with SKU and category)
  - Movement Type (color-coded badges)
  - Quantity (color-coded: green for +, red for -)
  - Reference (type and ID)
  - Performed By (user name and role)
  - Notes & expiry date
- Pagination (20 per page)
- Loads relationships efficiently (product.category, performedBy)

### 7. Routes & Navigation
**Routes:** Added to `routes/web.php`
```php
Route::middleware(['check.role:store_keeper,manager,admin'])->group(function () {
    Route::get('/stock-movements', StockMovements::class)->name('stock-movements.index');
});
```

**Sidebar Menu:** Added to `resources/views/layouts/sidebar-navigation.blade.php`
- Menu item: "Stock Movements"
- Visible to: admin, manager, store_keeper roles
- Icon: Clipboard with checkmark

---

## Installation & Setup Instructions

### 1. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery_erp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

This will:
- Create all tables including accounts, products, and stock_movements
- Seed default system accounts (CASH, BANK1, BANK2, SALES, PURCHASES, INVENTORY)
- Seed users, settings, and categories

### 4. Access the Application
- Default admin credentials will be created by UserSeeder
- Navigate to `/accounts` for Chart of Accounts management
- Navigate to `/stock-movements` for Stock Movement tracking

---

## Key Design Decisions

### 1. Append-Only Stock Movements
- Stock movements are never deleted or updated
- Creates complete audit trail
- Only created_at timestamp (no updated_at)

### 2. System Accounts Protection
- System accounts cannot be edited or deleted
- Ensures integrity of core accounting functions
- Custom accounts have full CRUD capabilities

### 3. Hierarchical Account Structure
- Self-referential parent_id allows account hierarchy
- Supports sub-accounts for better organization
- Cannot delete accounts with children

### 4. Service Layer for Stock Operations
- All stock changes go through InventoryService
- Ensures consistency and validation
- Uses DB transactions for data integrity
- Proper error handling with exceptions

### 5. Stock Quantity in Pieces
- All stock tracked in smallest unit (pieces)
- Products support box pricing with automatic conversion
- Simplifies calculations and reporting

### 6. Role-Based Access Control
- Accounts: admin, accountant only
- Stock Movements: admin, manager, store_keeper
- Follows principle of least privilege

---

## Next Steps (Future Enhancements)

1. **Transaction System**
   - Create journal entries for financial transactions
   - Link stock movements to accounting entries
   - Implement double-entry bookkeeping

2. **Product Management UI**
   - Full CRUD for products
   - Category assignment
   - Box/piece pricing configuration

3. **Stock Adjustment UI**
   - Manual stock adjustments through UI
   - Damage tracking interface
   - Batch and expiry date management

4. **Reporting**
   - Stock movement reports
   - Account balance reports
   - Low stock alerts
   - Financial statements

5. **GRN (Goods Receipt Note) System**
   - Create GRN when receiving stock
   - Automatically create stock movements
   - Link to purchase orders

6. **Sales Integration**
   - Automatically reduce stock on sale
   - Create stock movements for sales
   - Link to accounting entries

---

## Testing Checklist

### Accounts
- [ ] Create custom account
- [ ] Edit custom account
- [ ] Delete custom account (without children/transactions)
- [ ] Verify system accounts cannot be edited
- [ ] Verify system accounts cannot be deleted
- [ ] Test account hierarchy (parent/child)
- [ ] Test filters (search, type, status)
- [ ] Verify role-based access (admin, accountant only)

### Stock Movements
- [ ] Test InventoryService::addStock()
- [ ] Test InventoryService::reduceStock() with sufficient stock
- [ ] Test reduceStock() with insufficient stock (should fail)
- [ ] Test InventoryService::markAsDamaged()
- [ ] Test InventoryService::adjustStock() (positive and negative)
- [ ] Verify stock movements are append-only
- [ ] Test stock movements viewer filters
- [ ] Verify role-based access (admin, manager, store_keeper only)
- [ ] Check proper display of movement types and colors
- [ ] Verify user attribution (performed_by)

---

## Files Created/Modified

### New Files (23 total)
1. `database/migrations/2024_11_15_000004_create_accounts_table.php`
2. `database/migrations/2024_11_15_000004_create_products_table.php`
3. `database/migrations/2024_11_15_000005_create_stock_movements_table.php`
4. `database/seeders/AccountSeeder.php`
5. `app/Models/Account.php`
6. `app/Models/Product.php`
7. `app/Models/StockMovement.php`
8. `app/Services/InventoryService.php`
9. `app/Livewire/Accounts/AccountManagement.php`
10. `app/Livewire/Accounts/CreateAccount.php`
11. `app/Livewire/Accounts/EditAccount.php`
12. `app/Livewire/Inventory/StockMovements.php`
13. `resources/views/livewire/accounts/account-management.blade.php`
14. `resources/views/livewire/accounts/create-account.blade.php`
15. `resources/views/livewire/accounts/edit-account.blade.php`
16. `resources/views/livewire/inventory/stock-movements.blade.php`

### Modified Files (4 total)
1. `database/seeders/DatabaseSeeder.php` - Added AccountSeeder
2. `app/Models/Category.php` - Added products relationship
3. `routes/web.php` - Added routes for accounts and stock movements
4. `resources/views/layouts/sidebar-navigation.blade.php` - Added menu items

---

## Database Schema Summary

```
accounts
├── id (PK)
├── account_code (unique, indexed)
├── account_name
├── account_type (enum)
├── parent_id (FK → accounts.id, nullable)
├── is_system_account (boolean)
├── balance (decimal 15,2)
├── is_active (boolean)
└── timestamps

products
├── id (PK)
├── name
├── sku (unique)
├── category_id (FK → categories.id)
├── description (nullable)
├── unit_price (decimal 10,2)
├── box_price (decimal 10,2, nullable)
├── pieces_per_box (integer, nullable)
├── current_stock_quantity (decimal 10,2)
├── damaged_stock_quantity (decimal 10,2)
├── minimum_stock_level (decimal 10,2)
├── barcode (unique, nullable)
├── is_active (boolean)
└── timestamps

stock_movements
├── id (PK)
├── product_id (FK → products.id)
├── movement_type (enum)
├── quantity (decimal 10,2)
├── reference_type (enum, nullable)
├── reference_id (bigint, nullable)
├── batch_number (nullable)
├── expiry_date (date, nullable)
├── notes (text, nullable)
├── performed_by (FK → users.id)
└── created_at (timestamp only)
```

---

## Conclusion

This implementation provides a solid foundation for:
- **Financial Management**: Chart of accounts with hierarchy support
- **Inventory Tracking**: Comprehensive stock movement tracking with full audit trail
- **Data Integrity**: Service layer pattern, DB transactions, and validation
- **User Experience**: Clean UI with filtering, search, and role-based access
- **Scalability**: Ready for future enhancements (transactions, GRN, sales integration)

All code follows Laravel best practices, uses Tailwind CSS for styling, and maintains consistency with the existing codebase structure.
