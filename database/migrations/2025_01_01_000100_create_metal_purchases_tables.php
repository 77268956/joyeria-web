<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates metal_purchases, metal_purchase_items, and purchase_adjustments.
     */
    public function up(): void
    {
        Schema::create('metal_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->string('number', 30);
            $table->unique(['store_id', 'number']);

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('cash_session_id')
                ->constrained('cash_sessions')
                ->restrictOnDelete();

            $table->string('status', 30)->default('paid')
                ->comment('pending_approval | paid | rejected | adjusted');
            $table->decimal('total_amount', 12, 2);
            $table->string('payout_method', 20)->comment('cash | transfer');

            // Approval workflow for high-value purchases
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_note', 255)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'customer_id']);
        });

        DB::statement('ALTER TABLE metal_purchases ADD CONSTRAINT chk_purchases_total CHECK (total_amount >= 0)');

        Schema::create('metal_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metal_purchase_id')
                ->constrained('metal_purchases')
                ->restrictOnDelete();

            $table->foreignId('metal_id')
                ->constrained('metals')
                ->restrictOnDelete();
            $table->foreignId('purity_id')
                ->constrained('metal_purities')
                ->restrictOnDelete();

            $table->string('description', 150)->comment('Brief description of the piece');

            $table->decimal('gross_weight_g', 10, 3)->comment('Total weight including stones/filler');
            $table->decimal('net_weight_g', 10, 3)->comment('Pure-metal weight — base of calculation');
            $table->string('weight_source', 10)->comment('scale | manual');

            // Valuation snapshots — frozen at purchase time
            $table->decimal('fineness_snapshot', 5, 4)->comment('Purity used for calculation');
            $table->decimal('price_per_gram_snapshot', 12, 4)->comment('Price per pure gram used');
            $table->decimal('discount_percent_snapshot', 5, 2)->comment('Store margin applied');

            $table->decimal('amount', 12, 2)->comment('Final amount paid for this item');
            $table->string('photo_path', 255)->comment('Mandatory photo — stored on private disk');

            $table->string('destination', 20)->comment('used_inventory | melting');

            // Destination references (mutually exclusive; only one should be set)
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('material_lot_id')
                ->nullable()
                ->constrained('material_lots')
                ->restrictOnDelete();

            $table->index('metal_purchase_id');
        });

        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_gross_weight CHECK (gross_weight_g > 0)');
        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_net_weight CHECK (net_weight_g > 0)');
        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_net_le_gross CHECK (net_weight_g <= gross_weight_g)');
        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_amount CHECK (amount >= 0)');
        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_fineness CHECK (fineness_snapshot > 0 AND fineness_snapshot <= 1)');
        DB::statement('ALTER TABLE metal_purchase_items ADD CONSTRAINT chk_purchase_items_discount CHECK (discount_percent_snapshot >= 0 AND discount_percent_snapshot <= 100)');

        Schema::create('purchase_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metal_purchase_id')
                ->constrained('metal_purchases')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('reason', 255)->comment('Mandatory justification for any adjustment');
            $table->json('old_values')->comment('Snapshot of values before the adjustment');
            $table->json('new_values')->comment('Snapshot of values after the adjustment');
            $table->decimal('amount_difference', 12, 2)->comment('Monetary difference (may be negative)');

            // Immutable — no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index('metal_purchase_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_adjustments');
        Schema::dropIfExists('metal_purchase_items');
        Schema::dropIfExists('metal_purchases');
    }
};
