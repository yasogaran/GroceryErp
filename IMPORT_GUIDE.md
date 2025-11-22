# GroceryERP - CSV Import Guide

Complete guide for importing Categories, Products, and Stock using CSV files.

---

## Table of Contents

1. [Overview](#overview)
2. [Accessing Import Features](#accessing-import-features)
3. [Import Categories](#import-categories)
4. [Import Products](#import-products)
5. [Import Stock](#import-stock)
6. [Common Errors & Troubleshooting](#common-errors--troubleshooting)
7. [Best Practices](#best-practices)

---

## Overview

The CSV import feature allows you to bulk upload data into GroceryERP at the initial stage of your project setup. This is particularly useful for:

- **Initial Data Setup**: Quickly populate your system with existing inventory data
- **Bulk Operations**: Add multiple items at once instead of entering them one by one
- **Data Migration**: Transfer data from other systems

### Key Features

✅ **Download Templates**: Pre-formatted CSV files with sample data
✅ **Validation**: Real-time error checking before import
✅ **Error Reports**: Detailed error messages for each row
✅ **Safe Import**: Database transactions ensure data integrity
✅ **No External Dependencies**: Works with standard CSV files (Excel compatible)

### Access Requirements

**User Roles**: Admin, Manager, Store Keeper

---

## Accessing Import Features

### Via Sidebar Navigation

**Import Categories**
1. Navigate to sidebar → **Product Management** section
2. Click **"Import Categories"**

**Import Products**
1. Navigate to sidebar → **Product Management** section
2. Click **"Import Products"**

**Import Stock**
1. Navigate to sidebar → **Inventory & Receiving** section
2. Click **"Import Stock"**

### Direct URLs

- Categories: `/categories/import`
- Products: `/products/import`
- Stock: `/stocks/import`

---

## Import Categories

Use this feature to bulk upload product categories and subcategories.

### Step 1: Download Template

1. Click the **"Download Template"** button
2. Save the file as `categories_template.csv`
3. Open with Excel, Google Sheets, or any spreadsheet software

### Step 2: Fill in Category Data

The template contains the following columns:

| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| **name** | ✅ Yes | Category name | Beverages |
| **parent_category** | ❌ No | Parent category name (for subcategories) | Beverages |
| **description** | ❌ No | Category description | All types of beverages |
| **is_active** | ✅ Yes | Active status: yes/no or 1/0 or true/false | yes |

#### Example Data

```csv
name,parent_category,description,is_active
Beverages,,All types of beverages,yes
Soft Drinks,Beverages,Carbonated and non-carbonated drinks,yes
Energy Drinks,Beverages,High-caffeine beverages,yes
Dairy,,Milk and dairy products,yes
Fresh Milk,Dairy,Pasteurized and UHT milk,yes
Snacks,,Chips and biscuits,yes
```

### Step 3: Upload & Validate

1. Click **"Choose File"** and select your CSV
2. Click **"Process File"**
3. Review validation results:
   - **Green rows**: Valid, will be imported
   - **Red rows**: Contains errors, will be skipped

### Step 4: Import

1. Review the summary (Total, Valid, Invalid counts)
2. Check valid rows table
3. Fix any errors in invalid rows (re-upload if needed)
4. Click **"Import X Categories"**
5. Wait for success message

### Category-Specific Rules

- ✅ Category names must be unique
- ✅ Parent categories are imported first, then child categories
- ✅ Parent category must exist (either in import file or database)
- ❌ Duplicate category names will be rejected
- ❌ Parent category not found will cause error

---

## Import Products

Use this feature to bulk upload products with pricing and inventory settings.

### Step 1: Download Template

1. Click the **"Download Template"** button
2. Save the file as `products_template.csv`
3. Open with your preferred spreadsheet software

### Step 2: Fill in Product Data

The template contains the following columns:

| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| **sku** | ❌ No | Product SKU (auto-generated if empty) | PROD001 |
| **barcode** | ❌ No | Product barcode | 1234567890123 |
| **name** | ✅ Yes | Product name | Coca Cola 500ml |
| **description** | ❌ No | Product description | Carbonated soft drink |
| **category** | ✅ Yes | Category name (must exist) | Beverages |
| **brand** | ❌ No | Brand name | Coca Cola |
| **base_unit** | ✅ Yes | Unit of measurement | piece |
| **min_selling_price** | ✅ Yes | Minimum selling price | 45.00 |
| **max_selling_price** | ✅ Yes | Maximum selling price (MRP) | 50.00 |
| **reorder_level** | ❌ No | Reorder level quantity | 20 |
| **is_active** | ✅ Yes | Active status: yes/no or 1/0 or true/false | yes |

#### Valid Base Units

- `piece` - Individual items
- `kg` - Kilogram
- `gram` - Gram
- `liter` - Liter
- `ml` - Milliliter
- `meter` - Meter
- `cm` - Centimeter
- `box` - Box/Carton
- `pack` - Pack/Bundle

#### Example Data

```csv
sku,barcode,name,description,category,brand,base_unit,min_selling_price,max_selling_price,reorder_level,is_active
PROD001,1234567890123,Coca Cola 500ml,Carbonated soft drink,Beverages,Coca Cola,piece,45.00,50.00,20,yes
PROD002,1234567890124,Lays Chips 100g,Classic salted potato chips,Snacks,Lays,piece,40.00,45.00,30,yes
,1234567890125,Pepsi 500ml,Carbonated soft drink,Beverages,Pepsi,piece,42.00,48.00,25,yes
PROD004,,Amul Milk 1L,Full cream milk,Dairy,Amul,liter,55.00,60.00,15,yes
```

### Step 3: Upload & Validate

1. Click **"Choose File"** and select your CSV
2. Click **"Process File"**
3. Review validation results

The system will check for:
- Required fields completion
- Category existence
- Duplicate SKU/Barcode/Names
- Valid base units
- Price range (min ≤ max)

### Step 4: Import

1. Review the validation summary
2. Fix any errors in your CSV file
3. Click **"Import X Products"**
4. Products will be created with initial stock quantity of 0

### Product-Specific Rules

- ✅ SKU is auto-generated if left empty
- ✅ Categories must exist before importing products
- ✅ Min price must be ≤ Max price
- ❌ Duplicate SKU will be rejected
- ❌ Duplicate barcode will be rejected
- ❌ Duplicate product name will be rejected
- ❌ Invalid category will cause error

**Important**: Create categories first before importing products!

---

## Import Stock

Use this feature to add initial stock quantities to existing products.

### Step 1: Download Template

1. Click the **"Download Template"** button
2. Save the file as `stock_template.csv`
3. Open with your preferred spreadsheet software

### Step 2: Fill in Stock Data

The template contains the following columns:

| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| **product_sku** | ❌ No | Product SKU for identification | PROD001 |
| **product_name** | ✅ Yes | Product name (must exist) | Coca Cola 500ml |
| **quantity** | ✅ Yes | Stock quantity to add | 100 |
| **unit_cost** | ✅ Yes | Cost per unit | 35.00 |
| **supplier_name** | ❌ No | Supplier name | ABC Distributors |
| **batch_number** | ❌ No | Batch/Lot number | BATCH001 |
| **manufacturing_date** | ❌ No | Manufacturing date (YYYY-MM-DD) | 2024-01-15 |
| **expiry_date** | ❌ No | Expiry date (YYYY-MM-DD) | 2025-01-15 |
| **notes** | ❌ No | Additional notes | Initial stock |

#### Example Data

```csv
product_sku,product_name,quantity,unit_cost,supplier_name,batch_number,manufacturing_date,expiry_date,notes
PROD001,Coca Cola 500ml,100,35.00,ABC Distributors,BATCH001,2024-01-15,2025-01-15,Initial stock
PROD002,Lays Chips 100g,200,28.00,XYZ Suppliers,BATCH002,2024-01-10,2024-12-31,Bulk order
,Fresh Milk 1L,50,45.00,Dairy Farm,,,2024-02-28,Perishable item
PROD004,Amul Milk 1L,75,48.00,Amul Dairy,LOT2024A,2024-01-20,2024-02-20,Fresh stock
```

### Step 3: Upload & Validate

1. Click **"Choose File"** and select your CSV
2. Click **"Process File"**
3. Review validation results

The system will check for:
- Product existence (by SKU or name)
- Quantity > 0
- Valid date formats
- Manufacturing date < Expiry date

### Step 4: Import

1. Review the validation summary
2. Click **"Import Stock for X Products"**
3. Stock will be added using the existing InventoryService

### Stock-Specific Rules

- ✅ Product can be identified by SKU OR name
- ✅ Supplier will be linked if exists in database
- ✅ New supplier names will be saved (not created as supplier records)
- ✅ Batch tracking is automatic (FIFO system)
- ❌ Product must exist in database
- ❌ Quantity must be greater than 0
- ❌ Manufacturing date must be before expiry date

**Important**: Create products first before importing stock!

---

## Common Errors & Troubleshooting

### Categories Import

| Error | Cause | Solution |
|-------|-------|----------|
| "Category already exists" | Duplicate name in database | Use a different name or update manually |
| "Parent category not found" | Parent doesn't exist | Create parent category first or leave blank |
| "Name is required" | Empty name field | Fill in the category name |

### Products Import

| Error | Cause | Solution |
|-------|-------|----------|
| "Category does not exist" | Category not in database | Import categories first |
| "SKU already exists" | Duplicate SKU | Change SKU or leave empty for auto-generation |
| "Barcode already exists" | Duplicate barcode | Change barcode or remove it |
| "Product name already exists" | Duplicate product name | Use a unique product name |
| "Min price > Max price" | Invalid price range | Ensure min_selling_price ≤ max_selling_price |
| "Invalid base_unit" | Unknown unit | Use valid units: piece, kg, gram, liter, ml, meter, cm, box, pack |

### Stock Import

| Error | Cause | Solution |
|-------|-------|----------|
| "Product not found" | Product doesn't exist | Import products first |
| "Quantity must be > 0" | Invalid quantity | Enter a positive number |
| "Manufacturing date after expiry" | Invalid date range | Check date format (YYYY-MM-DD) and order |
| "Invalid date format" | Wrong date format | Use YYYY-MM-DD format (e.g., 2024-01-15) |

### File Format Issues

| Issue | Solution |
|-------|----------|
| File upload fails | Ensure file is .csv or .txt format, max 2MB |
| Special characters not showing | Save CSV with UTF-8 encoding |
| Excel changes dates | Format date cells as text before entering dates |
| Comma in values | Enclose value in quotes: "Description, with comma" |

---

## Best Practices

### 1. Import Order

Always follow this sequence:

```
1. Categories (parent categories first)
   ↓
2. Products (with category references)
   ↓
3. Stock (with product references)
```

### 2. Preparing Your CSV Files

**Excel Users:**
- Open the downloaded template
- Fill in your data
- Save As → CSV UTF-8 (Comma delimited) (.csv)
- Do NOT save as .xlsx

**Google Sheets Users:**
- Import the downloaded template
- Fill in your data
- File → Download → Comma Separated Values (.csv)

**Encoding:**
- Always use UTF-8 encoding
- Templates are pre-configured with UTF-8 BOM for Excel compatibility

### 3. Data Validation Tips

**Before Upload:**
- ✅ Remove empty rows at the bottom
- ✅ Check for duplicate values (names, SKUs, barcodes)
- ✅ Verify category names match exactly
- ✅ Ensure date format is YYYY-MM-DD
- ✅ Use decimal points for prices (45.00, not 45,00)
- ✅ Check that all required fields are filled

**After Validation:**
- ✅ Review invalid rows carefully
- ✅ Fix errors in original CSV file
- ✅ Re-upload corrected file
- ✅ Only valid rows will be imported

### 4. Handling Large Datasets

**For 100+ Items:**
- Split into smaller batches (50-100 rows per file)
- Import in stages
- Verify each batch before proceeding

**For Hierarchical Categories:**
- Import all parent categories first
- Then import child categories in a second file

**For Products with Complex Relationships:**
- Ensure all referenced categories exist
- Consider importing simple products first
- Add complex ones (with packaging) later

### 5. Testing Your Import

**Recommended Workflow:**
1. Download template
2. Add 2-3 test rows
3. Upload and validate
4. Import test data
5. Verify in the system
6. Proceed with full dataset

### 6. Backup Before Import

**Safety Measures:**
- Create a database backup before large imports
- Use Admin → Backups feature
- Keep original CSV files for reference

### 7. Date Formatting

**Always use ISO format:**
```
✅ Correct:  2024-01-15
❌ Wrong:    15/01/2024
❌ Wrong:    Jan 15, 2024
❌ Wrong:    15-01-2024
```

### 8. Handling Special Characters

**If your data contains:**
- Commas: Enclose in quotes → `"Description, with comma"`
- Quotes: Use double quotes → `"Product ""Special"" Edition"`
- Line breaks: Avoid or use quotes

---

## Sample Import Workflow

Here's a complete example of setting up a new grocery store:

### Step 1: Import Categories (5 categories)

```csv
name,parent_category,description,is_active
Beverages,,All drinks and beverages,yes
Dairy,,Milk and dairy products,yes
Snacks,,Chips and biscuits,yes
Soft Drinks,Beverages,Carbonated drinks,yes
Fresh Milk,Dairy,Pasteurized milk,yes
```

**Result:** 5 categories imported

### Step 2: Import Products (10 products)

```csv
sku,barcode,name,description,category,brand,base_unit,min_selling_price,max_selling_price,reorder_level,is_active
,1001,Coca Cola 500ml,Soft drink,Soft Drinks,Coca Cola,piece,40.00,45.00,50,yes
,1002,Pepsi 500ml,Soft drink,Soft Drinks,Pepsi,piece,40.00,45.00,50,yes
,1003,Sprite 500ml,Lemon drink,Soft Drinks,Coca Cola,piece,38.00,42.00,40,yes
,2001,Amul Milk 1L,Full cream,Fresh Milk,Amul,liter,52.00,56.00,30,yes
,2002,Mother Dairy 1L,Toned milk,Fresh Milk,Mother Dairy,liter,48.00,52.00,30,yes
,3001,Lays Classic 50g,Salted chips,Snacks,Lays,piece,18.00,20.00,100,yes
,3002,Kurkure 50g,Masala puffs,Snacks,Kurkure,piece,18.00,20.00,80,yes
```

**Result:** 7 products imported

### Step 3: Import Initial Stock

```csv
product_sku,product_name,quantity,unit_cost,supplier_name,batch_number,manufacturing_date,expiry_date,notes
,Coca Cola 500ml,200,32.00,Beverage Distributors,CC2024A,2024-01-01,2024-12-31,Initial stock
,Pepsi 500ml,150,32.00,Beverage Distributors,PP2024A,2024-01-01,2024-12-31,Initial stock
,Sprite 500ml,120,30.00,Beverage Distributors,SP2024A,2024-01-05,2024-12-31,Initial stock
,Amul Milk 1L,80,45.00,Dairy Supplier,AM2024,2024-01-20,2024-02-10,Fresh batch
,Mother Dairy 1L,70,42.00,Dairy Supplier,MD2024,2024-01-20,2024-02-10,Fresh batch
,Lays Classic 50g,300,15.00,Snacks Wholesaler,LC2024,2024-01-10,2024-06-30,Bulk order
,Kurkure 50g,250,15.00,Snacks Wholesaler,KK2024,2024-01-10,2024-06-30,Bulk order
```

**Result:** 7 stock entries imported, inventory updated

### Final Result

✅ **5 Categories** organized hierarchically
✅ **7 Products** with pricing and reorder levels
✅ **1,170 Total Units** in stock across all products
✅ **Ready for POS operations**

---

## Support & Additional Resources

### Need Help?

1. **Check this guide** for common issues
2. **Review validation errors** - they contain specific solutions
3. **Start with small test imports** before bulk operations
4. **Keep your CSV files** for reference and re-import if needed

### Related Features

- **Manual Entry**: Use Create buttons for individual items
- **Barcode Labels**: Generate labels after importing products
- **Stock Adjustments**: Fine-tune quantities after import
- **Reports**: Verify imported data using Stock Reports

### Tips for Success

✨ **Start small** - Test with 5-10 rows first
✨ **Validate often** - Check after each section
✨ **Keep backups** - Save original CSV files
✨ **Follow order** - Categories → Products → Stock
✨ **Use templates** - They're pre-formatted correctly

---

**Document Version:** 1.0
**Last Updated:** 2024-01-22
**Compatible with:** GroceryERP v1.0+

For technical support or feature requests, contact your system administrator.
