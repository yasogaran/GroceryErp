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
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('payment_mode')->constrained('accounts')->onDelete('restrict');
            $table->index('bank_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropIndex(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
