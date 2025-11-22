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
            // Add supplier_id as foreign key (nullable for backwards compatibility)
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('reference_id')
                ->constrained('suppliers')
                ->onDelete('restrict');

            // Add supplier_name for denormalization (faster lookups, preserves historical data)
            $table->string('supplier_name', 255)
                ->nullable()
                ->after('supplier_id');

            // Add index for performance
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropColumn(['supplier_id', 'supplier_name']);
        });
    }
};
