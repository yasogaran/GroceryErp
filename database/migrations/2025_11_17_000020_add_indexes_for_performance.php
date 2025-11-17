<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Safely add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, $columns, ?string $customName = null): void
    {
        // Generate index name
        if ($customName) {
            $indexName = $customName;
        } else {
            $columnStr = is_array($columns) ? implode('_', $columns) : $columns;
            $indexName = "{$table}_{$columnStr}_index";
        }

        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->index($columns);
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales table indexes
        $this->addIndexIfNotExists('sales', 'sale_date');
        $this->addIndexIfNotExists('sales', 'shift_id');
        $this->addIndexIfNotExists('sales', 'customer_id');
        $this->addIndexIfNotExists('sales', ['sale_date', 'shift_id']);
        $this->addIndexIfNotExists('sales', 'created_at');

        // Sale items table indexes
        $this->addIndexIfNotExists('sale_items', 'sale_id');
        $this->addIndexIfNotExists('sale_items', 'product_id');
        $this->addIndexIfNotExists('sale_items', ['sale_id', 'product_id']);

        // Products table indexes
        $this->addIndexIfNotExists('products', 'category_id');
        $this->addIndexIfNotExists('products', 'sku');
        $this->addIndexIfNotExists('products', 'barcode');
        $this->addIndexIfNotExists('products', 'is_active');
        $this->addIndexIfNotExists('products', ['category_id', 'is_active']);
        $this->addIndexIfNotExists('products', 'reorder_level');

        // Stock movements table indexes
        // Note: product_id, movement_type, created_at already have indexes from table creation
        // Only add composite indexes that don't exist
        $this->addIndexIfNotExists('stock_movements', ['product_id', 'movement_type']);
        $this->addIndexIfNotExists('stock_movements', 'batch_number'); // For batch tracking
        $this->addIndexIfNotExists('stock_movements', 'expiry_date'); // For expiry reports

        // Journal entry lines table indexes
        $this->addIndexIfNotExists('journal_entry_lines', 'journal_entry_id');
        $this->addIndexIfNotExists('journal_entry_lines', 'account_id');
        $this->addIndexIfNotExists('journal_entry_lines', ['journal_entry_id', 'account_id']);

        // Journal entries table indexes
        $this->addIndexIfNotExists('journal_entries', 'entry_date');
        $this->addIndexIfNotExists('journal_entries', 'entry_type');
        $this->addIndexIfNotExists('journal_entries', 'status');
        $this->addIndexIfNotExists('journal_entries', ['entry_date', 'status']);

        // Accounts table indexes
        // Note: account_type and parent_id already have indexes from table creation
        // Only add indexes that don't exist
        $this->addIndexIfNotExists('accounts', 'is_active');
        $this->addIndexIfNotExists('accounts', ['account_type', 'is_active']);

        // GRNs table indexes
        $this->addIndexIfNotExists('grns', 'supplier_id');
        $this->addIndexIfNotExists('grns', 'grn_date');
        $this->addIndexIfNotExists('grns', 'status');
        $this->addIndexIfNotExists('grns', ['supplier_id', 'grn_date']);

        // GRN items table indexes
        $this->addIndexIfNotExists('grn_items', 'grn_id');
        $this->addIndexIfNotExists('grn_items', 'product_id');
        $this->addIndexIfNotExists('grn_items', ['grn_id', 'product_id']);

        // Customers table indexes
        $this->addIndexIfNotExists('customers', 'phone');
        $this->addIndexIfNotExists('customers', 'email');
        $this->addIndexIfNotExists('customers', 'loyalty_points');

        // Shifts table indexes
        // Note: cashier_id, shift_start, is_verified already have indexes from table creation
        // Add composite index for shift history queries
        $this->addIndexIfNotExists('shifts', ['cashier_id', 'shift_start']);

        // Sale returns table indexes
        $this->addIndexIfNotExists('sale_returns', 'sale_id');
        $this->addIndexIfNotExists('sale_returns', 'return_date');
        $this->addIndexIfNotExists('sale_returns', ['sale_id', 'return_date']);

        // Suppliers table indexes
        $this->addIndexIfNotExists('suppliers', 'email');
        $this->addIndexIfNotExists('suppliers', 'phone');
        $this->addIndexIfNotExists('suppliers', 'is_active');

        // Offers table indexes
        $this->addIndexIfNotExists('offers', 'start_date');
        $this->addIndexIfNotExists('offers', 'end_date');
        $this->addIndexIfNotExists('offers', 'is_active');
        $this->addIndexIfNotExists('offers', ['start_date', 'end_date', 'is_active']);

        // Categories table indexes
        $this->addIndexIfNotExists('categories', 'parent_id');
        $this->addIndexIfNotExists('categories', 'is_active');

        // Settings table indexes
        $this->addIndexIfNotExists('settings', 'key');
        $this->addIndexIfNotExists('settings', 'group_name');
    }

    /**
     * Safely drop index if it exists
     */
    private function dropIndexIfExists(string $table, $columns): void
    {
        $columnStr = is_array($columns) ? implode('_', $columns) : $columns;
        $indexName = "{$table}_{$columnStr}_index";

        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->dropIndex(is_array($columns) ? $columns : [$columns]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sales table indexes
        $this->dropIndexIfExists('sales', 'sale_date');
        $this->dropIndexIfExists('sales', 'shift_id');
        $this->dropIndexIfExists('sales', 'customer_id');
        $this->dropIndexIfExists('sales', ['sale_date', 'shift_id']);
        $this->dropIndexIfExists('sales', 'created_at');

        // Sale items table indexes
        $this->dropIndexIfExists('sale_items', 'sale_id');
        $this->dropIndexIfExists('sale_items', 'product_id');
        $this->dropIndexIfExists('sale_items', ['sale_id', 'product_id']);

        // Products table indexes
        $this->dropIndexIfExists('products', 'category_id');
        $this->dropIndexIfExists('products', 'sku');
        $this->dropIndexIfExists('products', 'barcode');
        $this->dropIndexIfExists('products', 'is_active');
        $this->dropIndexIfExists('products', ['category_id', 'is_active']);
        $this->dropIndexIfExists('products', 'reorder_level');

        // Stock movements table indexes
        $this->dropIndexIfExists('stock_movements', ['product_id', 'movement_type']);
        $this->dropIndexIfExists('stock_movements', 'batch_number');
        $this->dropIndexIfExists('stock_movements', 'expiry_date');

        // Journal entry lines table indexes
        $this->dropIndexIfExists('journal_entry_lines', 'journal_entry_id');
        $this->dropIndexIfExists('journal_entry_lines', 'account_id');
        $this->dropIndexIfExists('journal_entry_lines', ['journal_entry_id', 'account_id']);

        // Journal entries table indexes
        $this->dropIndexIfExists('journal_entries', 'entry_date');
        $this->dropIndexIfExists('journal_entries', 'entry_type');
        $this->dropIndexIfExists('journal_entries', 'status');
        $this->dropIndexIfExists('journal_entries', ['entry_date', 'status']);

        // Accounts table indexes
        $this->dropIndexIfExists('accounts', 'is_active');
        $this->dropIndexIfExists('accounts', ['account_type', 'is_active']);

        // GRNs table indexes
        $this->dropIndexIfExists('grns', 'supplier_id');
        $this->dropIndexIfExists('grns', 'grn_date');
        $this->dropIndexIfExists('grns', 'status');
        $this->dropIndexIfExists('grns', ['supplier_id', 'grn_date']);

        // GRN items table indexes
        $this->dropIndexIfExists('grn_items', 'grn_id');
        $this->dropIndexIfExists('grn_items', 'product_id');
        $this->dropIndexIfExists('grn_items', ['grn_id', 'product_id']);

        // Customers table indexes
        $this->dropIndexIfExists('customers', 'phone');
        $this->dropIndexIfExists('customers', 'email');
        $this->dropIndexIfExists('customers', 'loyalty_points');

        // Shifts table indexes
        $this->dropIndexIfExists('shifts', ['cashier_id', 'shift_start']);

        // Sale returns table indexes
        $this->dropIndexIfExists('sale_returns', 'sale_id');
        $this->dropIndexIfExists('sale_returns', 'return_date');
        $this->dropIndexIfExists('sale_returns', ['sale_id', 'return_date']);

        // Suppliers table indexes
        $this->dropIndexIfExists('suppliers', 'email');
        $this->dropIndexIfExists('suppliers', 'phone');
        $this->dropIndexIfExists('suppliers', 'is_active');

        // Offers table indexes
        $this->dropIndexIfExists('offers', 'start_date');
        $this->dropIndexIfExists('offers', 'end_date');
        $this->dropIndexIfExists('offers', 'is_active');
        $this->dropIndexIfExists('offers', ['start_date', 'end_date', 'is_active']);

        // Categories table indexes
        $this->dropIndexIfExists('categories', 'parent_id');
        $this->dropIndexIfExists('categories', 'is_active');

        // Settings table indexes
        $this->dropIndexIfExists('settings', 'key');
        $this->dropIndexIfExists('settings', 'group_name');
    }
};
