<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales table indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_date');
            $table->index('shift_id');
            $table->index('customer_id');
            $table->index(['sale_date', 'shift_id']); // Composite index for daily reports
            $table->index('created_at');
        });

        // Sale items table indexes
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('product_id');
            $table->index(['sale_id', 'product_id']); // Composite index
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('sku');
            $table->index('barcode');
            $table->index('is_active');
            $table->index(['category_id', 'is_active']); // Composite index for filtering
            $table->index('reorder_level'); // For low stock queries
        });

        // Stock movements table indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('batch_id');
            $table->index('movement_type');
            $table->index('movement_date');
            $table->index(['product_id', 'movement_type']); // Composite index
            $table->index(['product_id', 'movement_date']); // For stock reports
        });

        // Journal entry lines table indexes (transactions)
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index('journal_entry_id');
            $table->index('account_id');
            $table->index(['journal_entry_id', 'account_id']); // Composite index
        });

        // Journal entries table indexes
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index('entry_date');
            $table->index('entry_type');
            $table->index('status');
            $table->index(['entry_date', 'status']); // For reports
        });

        // Accounts table indexes
        Schema::table('accounts', function (Blueprint $table) {
            $table->index('account_type');
            $table->index('parent_account_id');
            $table->index('is_active');
            $table->index(['account_type', 'is_active']); // Composite index
        });

        // GRNs table indexes
        Schema::table('grns', function (Blueprint $table) {
            $table->index('supplier_id');
            $table->index('grn_date');
            $table->index('status');
            $table->index(['supplier_id', 'grn_date']); // For supplier reports
        });

        // GRN items table indexes
        Schema::table('grn_items', function (Blueprint $table) {
            $table->index('grn_id');
            $table->index('product_id');
            $table->index(['grn_id', 'product_id']); // Composite index
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone');
            $table->index('email');
            $table->index('loyalty_points');
        });

        // Shifts table indexes
        Schema::table('shifts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('shift_date');
            $table->index('status');
            $table->index(['user_id', 'shift_date']); // For user shift history
        });

        // Sale returns table indexes
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('return_date');
            $table->index(['sale_id', 'return_date']); // Composite index
        });

        // Suppliers table indexes
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('email');
            $table->index('phone');
            $table->index('is_active');
        });

        // Offers table indexes
        Schema::table('offers', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_active');
            $table->index(['start_date', 'end_date', 'is_active']); // For active offers query
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id');
            $table->index('is_active');
        });

        // Settings table indexes
        Schema::table('settings', function (Blueprint $table) {
            $table->index('key');
            $table->index('group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sales table indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['sale_date']);
            $table->dropIndex(['shift_id']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['sale_date', 'shift_id']);
            $table->dropIndex(['created_at']);
        });

        // Sale items table indexes
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['sale_id', 'product_id']);
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['barcode']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['reorder_level']);
        });

        // Stock movements table indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['batch_id']);
            $table->dropIndex(['movement_type']);
            $table->dropIndex(['movement_date']);
            $table->dropIndex(['product_id', 'movement_type']);
            $table->dropIndex(['product_id', 'movement_date']);
        });

        // Journal entry lines table indexes
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex(['journal_entry_id']);
            $table->dropIndex(['account_id']);
            $table->dropIndex(['journal_entry_id', 'account_id']);
        });

        // Journal entries table indexes
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['entry_date']);
            $table->dropIndex(['entry_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['entry_date', 'status']);
        });

        // Accounts table indexes
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['account_type']);
            $table->dropIndex(['parent_account_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['account_type', 'is_active']);
        });

        // GRNs table indexes
        Schema::table('grns', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['grn_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['supplier_id', 'grn_date']);
        });

        // GRN items table indexes
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropIndex(['grn_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['grn_id', 'product_id']);
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['email']);
            $table->dropIndex(['loyalty_points']);
        });

        // Shifts table indexes
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['shift_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'shift_date']);
        });

        // Sale returns table indexes
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['return_date']);
            $table->dropIndex(['sale_id', 'return_date']);
        });

        // Suppliers table indexes
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['is_active']);
        });

        // Offers table indexes
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['start_date', 'end_date', 'is_active']);
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['is_active']);
        });

        // Settings table indexes
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['key']);
            $table->dropIndex(['group_name']);
        });
    }
};
