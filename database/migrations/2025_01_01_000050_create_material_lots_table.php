<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->string('code', 40)->comment('Unique lot code within a store');
            $table->foreignId('metal_id')->constrained('metals')->restrictOnDelete();
            $table->foreignId('purity_id')->constrained('metal_purities')->restrictOnDelete();
            $table->decimal('initial_weight_g', 10, 3)->comment('Weight at lot creation');
            $table->decimal('current_weight_g', 10, 3)->comment('Updated only via InventoryService');
            $table->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->string('status', 30)->default('open')->comment('open | sent_to_melting | closed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'code']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'metal_id', 'purity_id']);
        });

        DB::statement('ALTER TABLE material_lots ADD CONSTRAINT chk_material_lots_initial_weight CHECK (initial_weight_g > 0)');
        DB::statement('ALTER TABLE material_lots ADD CONSTRAINT chk_material_lots_current_weight CHECK (current_weight_g >= 0)');

        // Trigger: prevent current_weight_g from going below zero
        DB::unprepared('
            CREATE TRIGGER trg_material_lots_no_negative_weight
            BEFORE UPDATE ON material_lots
            FOR EACH ROW
            BEGIN
                IF NEW.current_weight_g < 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "current_weight_g cannot be negative in material_lots";
                END IF;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_material_lots_no_negative_weight');
        Schema::dropIfExists('material_lots');
    }
};
