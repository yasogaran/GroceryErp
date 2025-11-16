<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum column
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN movement_type ENUM('in', 'out', 'adjustment', 'damage', 'write_off', 'return') COMMENT 'in=stock increase, out=stock decrease, adjustment=manual adjustment, damage=mark as damaged, write_off=remove damaged stock, return=customer return restock'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN movement_type ENUM('in', 'out', 'adjustment') COMMENT 'in=stock increase, out=stock decrease, adjustment=manual adjustment'");
    }
};
