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
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('received_boxes')->default(0)->comment('For display only');
            $table->decimal('received_pieces', 10, 2)->comment('Actual stock update');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('batch_number', 100)->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('grn_id');
            $table->index('batch_number');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
