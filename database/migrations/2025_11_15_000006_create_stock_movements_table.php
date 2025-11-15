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
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'adjustment'])->comment('in=stock increase, out=stock decrease, adjustment=manual adjustment');
            $table->decimal('quantity', 10, 2)->comment('Always positive, type indicates direction');
            $table->string('reference_type', 50)->nullable()->comment('sale, grn, adjustment, damage, etc.');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of related record');
            $table->string('batch_number', 100)->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('product_id');
            $table->index('movement_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('batch_number');
            $table->index('expiry_date');
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
