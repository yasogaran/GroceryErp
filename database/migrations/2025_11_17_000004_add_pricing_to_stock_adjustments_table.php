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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Add pricing fields to track cost of adjustments
            $table->decimal('unit_cost', 10, 2)->nullable()->after('quantity')->comment('Purchase cost per unit');
            $table->decimal('min_selling_price', 10, 2)->nullable()->after('unit_cost')->comment('Minimum selling price');
            $table->decimal('max_selling_price', 10, 2)->nullable()->after('min_selling_price')->comment('Maximum selling price (MRP)');
            $table->string('batch_number')->nullable()->after('max_selling_price')->comment('Batch number for adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'min_selling_price', 'max_selling_price', 'batch_number']);
        });
    }
};
