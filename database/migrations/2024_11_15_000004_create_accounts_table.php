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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 50)->unique();
            $table->string('account_name');
            $table->enum('account_type', ['asset', 'liability', 'income', 'expense', 'equity']);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->boolean('is_system_account')->default(false);
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('account_code');
            $table->index('account_type');
            $table->index('parent_id');
            $table->index('is_system_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
