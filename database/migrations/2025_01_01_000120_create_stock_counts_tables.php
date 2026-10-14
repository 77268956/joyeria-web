<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates stock_counts and stock_count_items for physical inventory audits.
     *
     * A stock count compares expected stock (from inventory_movements) with what
     * is physically found. When applied, discrepancies generate adjustment movements.
     */
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->string('status', 30)->default('open')
                ->comment('open | finished | applied');

            $table->foreignId('started_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('started_at')->useCurrent();

            $table->timestamp('finished_at')->nullable();
            $table->foreignId('finished_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('applied_at')->nullable();
            $table->foreignId('applied_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('notes')->nullable();

            $table->index(['store_id', 'status']);
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')
                ->constrained('stock_counts')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->boolean('expected')->comment('True if system expects this piece to be present');
            $table->boolean('found')->nullable()->comment('True/false set during physical count; null if not counted yet');
            $table->string('note', 255)->nullable();

            $table->unique(['stock_count_id', 'product_id']);
            $table->index('stock_count_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
    }
};
