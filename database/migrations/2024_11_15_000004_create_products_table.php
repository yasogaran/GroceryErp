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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('unit_price', 10, 2); // Price per piece
            $table->decimal('box_price', 10, 2)->nullable(); // Price per box (with discount)
            $table->integer('pieces_per_box')->nullable(); // How many pieces in a box

            // Stock tracking
            $table->decimal('current_stock_quantity', 10, 2)->default(0); // In pieces
            $table->decimal('damaged_stock_quantity', 10, 2)->default(0); // In pieces
            $table->decimal('minimum_stock_level', 10, 2)->default(0); // Reorder point

            // Additional fields
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('category_id');
            $table->index('sku');
            $table->index('barcode');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
