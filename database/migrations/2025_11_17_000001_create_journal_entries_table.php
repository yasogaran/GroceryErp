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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->enum('entry_type', ['manual', 'sale', 'purchase', 'payment', 'return', 'adjustment'])->default('manual');
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->string('reference_type')->nullable(); // Sale, GRN, Expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('entry_number');
            $table->index('entry_date');
            $table->index('entry_type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
