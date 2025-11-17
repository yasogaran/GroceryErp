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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->after('quantity')->nullable()->comment('Purchase price (from supplier)');
            $table->decimal('min_selling_price', 10, 2)->after('unit_cost')->nullable()->comment('Minimum selling price for this batch');
            $table->decimal('max_selling_price', 10, 2)->after('min_selling_price')->nullable()->comment('Maximum selling price (MRP) for this batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'min_selling_price', 'max_selling_price']);
        });
    }
};
