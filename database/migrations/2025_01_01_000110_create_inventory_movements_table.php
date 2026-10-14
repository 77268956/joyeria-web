<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the inventory_movements table — the immutable source of truth for stock.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            // Either product_id or material_lot_id — never both, never neither
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('material_lot_id')
                ->nullable()
                ->constrained('material_lots')
                ->restrictOnDelete();

            $table->string('type', 30)
                ->comment('entry | purchase_entry | sale | sale_void | layaway_reserve | layaway_release | adjustment | to_melting');

            $table->smallInteger('quantity_delta')->default(0)
                ->comment('+1 or -1 for piece-level; 0 for bulk lots');
            $table->decimal('weight_delta_g', 10, 3)->default(0.000)
                ->comment('Signed weight change in grams for lot-level movements');

            // Polymorphic reference to originating document
            $table->string('reference_type', 60)->nullable()
                ->comment('e.g. App\\Models\\Sale or App\\Models\\MetalPurchase');
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('reason', 255)->nullable()
                ->comment('Required for adjustments');

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Immutable — only created_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['store_id', 'product_id']);
            $table->index(['store_id', 'material_lot_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'type']);
        });

        DB::statement('ALTER TABLE inventory_movements ADD CONSTRAINT chk_quantity_delta CHECK (quantity_delta IN (-1, 0, 1))');

        // Trigger: enforce that exactly one of product_id / material_lot_id is set
        DB::unprepared('
            CREATE TRIGGER trg_inventory_movements_xor_target
            BEFORE INSERT ON inventory_movements
            FOR EACH ROW
            BEGIN
                IF (NEW.product_id IS NULL AND NEW.material_lot_id IS NULL) THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "inventory_movements: product_id or material_lot_id must be set";
                END IF;
                IF (NEW.product_id IS NOT NULL AND NEW.material_lot_id IS NOT NULL) THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "inventory_movements: only one of product_id or material_lot_id may be set";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inventory_movements_xor_target');
        Schema::dropIfExists('inventory_movements');
    }
};
