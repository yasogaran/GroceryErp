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
        Schema::table('products', function (Blueprint $table) {
            // Note: reorder_level already exists from Phase 0
            $table->decimal('reorder_quantity', 10, 2)->default(0)->after('reorder_level')
                ->comment('Suggested quantity to reorder when stock is low');
            $table->boolean('enable_low_stock_alert')->default(true)->after('reorder_quantity')
                ->comment('Whether to send low stock alerts for this product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reorder_quantity', 'enable_low_stock_alert']);
        });
    }
};
