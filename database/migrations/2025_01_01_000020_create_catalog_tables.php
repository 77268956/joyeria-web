<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalog tables: metals, metal_purities, categories, locations.
     * These are shared across all stores (metals/categories) or scoped by store (locations).
     */
    public function up(): void
    {
        Schema::create('metals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('GOLD, SILVER, STEEL, FANTASY');
            $table->string('name', 50);
            $table->boolean('is_precious')->default(true)
                ->comment('Only gold and silver have a price per gram');
        });

        Schema::create('metal_purities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metal_id')
                ->constrained('metals')
                ->restrictOnDelete();
            $table->string('code', 10)->comment('10K, 14K, 18K, 24K, 925, 950');
            $table->string('label', 30)->comment('Display label, e.g. "Oro 18k"');
            $table->decimal('fineness', 5, 4)->comment('18K=0.7500, 925=0.9250');
            $table->boolean('is_active')->default(true);

            $table->index('metal_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE metal_purities ADD CONSTRAINT chk_metal_purities_fineness CHECK (fineness > 0 AND fineness <= 1.0000)');
        }

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();
            $table->string('name', 100);
            $table->string('type', 30)->comment('vitrina, caja_fuerte, taller, bodega');
            $table->boolean('is_active')->default(true);

            $table->index('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('metal_purities');
        Schema::dropIfExists('metals');
    }
};
