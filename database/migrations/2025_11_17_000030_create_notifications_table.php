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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g., 'low_stock', 'critical_stock', 'system_alert'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (product_id, etc.)
            $table->string('priority')->default('normal'); // normal, high, critical
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Specific user or null for all
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('type');
            $table->index('user_id');
            $table->index('is_read');
            $table->index(['user_id', 'is_read']); // Composite index for user's unread notifications
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
