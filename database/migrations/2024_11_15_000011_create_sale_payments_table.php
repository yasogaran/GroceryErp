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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->enum('payment_mode', ['cash', 'bank_transfer']);
            $table->unsignedBigInteger('bank_account_id')->nullable(); // For Phase 2
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('payment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
