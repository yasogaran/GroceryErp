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
        // Update all empty string barcodes to NULL in products table
        DB::table('products')
            ->where('barcode', '')
            ->update(['barcode' => null]);

        // Update all empty string package_barcodes to NULL in product_packaging table
        DB::table('product_packaging')
            ->where('package_barcode', '')
            ->update(['package_barcode' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - we don't want empty strings back
    }
};
