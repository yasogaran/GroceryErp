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
        Schema::table('sale_items', function (Blueprint $table) {
            // Add stock_movement_id to track which batch was used for this sale
            $table->foreignId('stock_movement_id')->nullable()->after('product_id')->constrained('stock_movements')->onDelete('set null');

            // Add unit_cost to calculate COGS (Cost of Goods Sold)
            $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price')->comment('Purchase cost for COGS calculation');

            // Add index for stock_movement_id
            $table->index('stock_movement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['stock_movement_id']);
            $table->dropIndex(['stock_movement_id']);
            $table->dropColumn(['stock_movement_id', 'unit_cost']);
        });
    }
};
