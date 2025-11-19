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
            // Add field to track which batch (stock IN movement) this OUT movement is depleting
            $table->foreignId('source_stock_movement_id')
                ->after('reference_id')
                ->nullable()
                ->comment('For OUT movements: references the stock IN movement (batch) being depleted')
                ->constrained('stock_movements')
                ->onDelete('restrict');
            
            // Add index for faster batch depletion queries
            $table->index('source_stock_movement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['source_stock_movement_id']);
            $table->dropColumn('source_stock_movement_id');
        });
    }
};
