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
        // Note: sale_date, shift_id, customer_id, status already have indexes from table creation
        $this->addIndexIfNotExists('sales', ['sale_date', 'shift_id']);
        $this->addIndexIfNotExists('sales', 'created_at');

        // Sale items table indexes
        // Note: sale_id, product_id already have indexes from table creation
        $this->addIndexIfNotExists('sale_items', ['sale_id', 'product_id']);

        // Products table indexes
        // Note: category_id, sku, barcode, is_active already have indexes from table creation
        $this->addIndexIfNotExists('products', ['category_id', 'is_active']);
        $this->addIndexIfNotExists('products', 'reorder_level');

        // Stock movements table indexes
        // Note: product_id, movement_type, created_at already have indexes from table creation
        $this->addIndexIfNotExists('stock_movements', ['product_id', 'movement_type']);
        $this->addIndexIfNotExists('stock_movements', 'batch_number');
        $this->addIndexIfNotExists('stock_movements', 'expiry_date');

        // Journal entry lines table indexes
        // Note: journal_entry_id, account_id already have indexes from table creation
        $this->addIndexIfNotExists('journal_entry_lines', ['journal_entry_id', 'account_id']);

        // Journal entries table indexes
        // Note: entry_date, entry_type, status already have indexes from table creation
        $this->addIndexIfNotExists('journal_entries', ['entry_date', 'status']);

        // Accounts table indexes
        // Note: account_type, parent_id already have indexes from table creation
        $this->addIndexIfNotExists('accounts', 'is_active');
        $this->addIndexIfNotExists('accounts', ['account_type', 'is_active']);

        // GRNs table indexes
        // Note: supplier_id, status already have indexes from table creation
        $this->addIndexIfNotExists('grns', 'grn_date');
        $this->addIndexIfNotExists('grns', ['supplier_id', 'grn_date']);

        // GRN items table indexes
        // Note: grn_id already has index from table creation
        $this->addIndexIfNotExists('grn_items', 'product_id');
        $this->addIndexIfNotExists('grn_items', ['grn_id', 'product_id']);

        // Customers table indexes
        // Note: phone already has index from table creation
        $this->addIndexIfNotExists('customers', 'email');
        $this->addIndexIfNotExists('customers', 'points_balance'); // For loyalty reports

        // Shifts table indexes
        // Note: cashier_id, shift_start, is_verified already have indexes from table creation
        $this->addIndexIfNotExists('shifts', ['cashier_id', 'shift_start']);

        // Sale returns table indexes
        // Note: original_sale_id, return_date already have indexes from table creation
        $this->addIndexIfNotExists('sale_returns', ['original_sale_id', 'return_date']);

        // Suppliers table indexes
        // Note: is_active already has index from table creation
        $this->addIndexIfNotExists('suppliers', 'email');
        $this->addIndexIfNotExists('suppliers', 'phone');

        // Offers table indexes
        // Note: is_active, ['start_date', 'end_date'] already have indexes from table creation
        $this->addIndexIfNotExists('offers', 'start_date');
        $this->addIndexIfNotExists('offers', 'end_date');
        $this->addIndexIfNotExists('offers', ['start_date', 'end_date', 'is_active']);

        // Categories table indexes
        // Note: is_active already has index from table creation
        $this->addIndexIfNotExists('categories', 'parent_id');

        // Settings table indexes
        // Note: key, group_name already have indexes from table creation
        // No additional indexes needed
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
        $this->dropIndexIfExists('sales', ['sale_date', 'shift_id']);
        $this->dropIndexIfExists('sales', 'created_at');

        // Sale items table indexes
        $this->dropIndexIfExists('sale_items', ['sale_id', 'product_id']);

        // Products table indexes
        $this->dropIndexIfExists('products', ['category_id', 'is_active']);
        $this->dropIndexIfExists('products', 'reorder_level');

        // Stock movements table indexes
        $this->dropIndexIfExists('stock_movements', ['product_id', 'movement_type']);
        $this->dropIndexIfExists('stock_movements', 'batch_number');
        $this->dropIndexIfExists('stock_movements', 'expiry_date');

        // Journal entry lines table indexes
        $this->dropIndexIfExists('journal_entry_lines', ['journal_entry_id', 'account_id']);

        // Journal entries table indexes
        $this->dropIndexIfExists('journal_entries', ['entry_date', 'status']);

        // Accounts table indexes
        $this->dropIndexIfExists('accounts', 'is_active');
        $this->dropIndexIfExists('accounts', ['account_type', 'is_active']);

        // GRNs table indexes
        $this->dropIndexIfExists('grns', 'grn_date');
        $this->dropIndexIfExists('grns', ['supplier_id', 'grn_date']);

        // GRN items table indexes
        $this->dropIndexIfExists('grn_items', 'product_id');
        $this->dropIndexIfExists('grn_items', ['grn_id', 'product_id']);

        // Customers table indexes
        $this->dropIndexIfExists('customers', 'email');
        $this->dropIndexIfExists('customers', 'points_balance');

        // Shifts table indexes
        $this->dropIndexIfExists('shifts', ['cashier_id', 'shift_start']);

        // Sale returns table indexes
        $this->dropIndexIfExists('sale_returns', ['original_sale_id', 'return_date']);

        // Suppliers table indexes
        $this->dropIndexIfExists('suppliers', 'email');
        $this->dropIndexIfExists('suppliers', 'phone');

        // Offers table indexes
        $this->dropIndexIfExists('offers', 'start_date');
        $this->dropIndexIfExists('offers', 'end_date');
        $this->dropIndexIfExists('offers', ['start_date', 'end_date', 'is_active']);

        // Categories table indexes
        $this->dropIndexIfExists('categories', 'parent_id');

        // Settings table indexes
        // No additional indexes to drop
    }
};
