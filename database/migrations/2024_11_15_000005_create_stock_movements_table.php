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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->enum('movement_type', ['in', 'out', 'adjustment', 'damage', 'return']);
            $table->decimal('quantity', 10, 2); // Positive for IN, Negative for OUT
            $table->enum('reference_type', ['sale', 'grn', 'adjustment', 'return'])->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('product_id');
            $table->index('movement_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('performed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
