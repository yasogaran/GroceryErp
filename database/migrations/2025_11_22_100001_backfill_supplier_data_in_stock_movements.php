<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Backfill supplier_id and supplier_name for existing stock_movements from GRNs
     */
    public function up(): void
    {
        // Only backfill for stock movements that reference GRNs
        DB::statement("
            UPDATE stock_movements sm
            INNER JOIN grns g ON sm.reference_id = g.id AND sm.reference_type = 'grn'
            INNER JOIN suppliers s ON g.supplier_id = s.id
            SET
                sm.supplier_id = s.id,
                sm.supplier_name = s.name
            WHERE sm.supplier_id IS NULL
        ");

        // Log the number of records updated
        $updatedCount = DB::affectedRows();
        if ($updatedCount > 0) {
            \Log::info("Backfilled supplier data for {$updatedCount} stock movements");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set supplier fields back to null for backfilled records
        DB::table('stock_movements')
            ->where('reference_type', 'grn')
            ->update([
                'supplier_id' => null,
                'supplier_name' => null,
            ]);
    }
};
