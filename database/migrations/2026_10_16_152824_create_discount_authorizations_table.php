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
        Schema::create('discount_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->cascadeOnDelete();

            $table->string('status', 20)->default('pending')
                ->comment('pending, approved, rejected, expired, used');

            $table->json('sale_snapshot')->comment('Full cart + payments payload frozen at request time');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('discount_percent', 5, 2);
            $table->string('reason')->comment('Seller justification for the discount');
            $table->string('rejection_reason')->nullable();

            $table->timestamp('expires_at')->comment('Auto-expire pending requests after 15 min');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_authorizations');
    }
};
