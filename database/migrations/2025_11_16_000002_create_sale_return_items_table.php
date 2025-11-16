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
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('sale_returns')->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('returned_quantity', 10, 2);
            $table->decimal('refund_amount', 10, 2);
            $table->boolean('is_damaged')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('return_id');
            $table->index('sale_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
