<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->string('sku', 40);
            $table->string('barcode', 60);
            $table->string('name', 150);
            $table->text('description')->nullable()->comment('Public-facing text for the website');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('metal_id')->constrained('metals')->restrictOnDelete();
            $table->foreignId('purity_id')->nullable()->constrained('metal_purities')->restrictOnDelete();
            $table->decimal('weight_g', 10, 3)->comment('Total piece weight in grams');
            $table->decimal('cost', 12, 2)->comment('Internal cost — never exposed publicly');
            $table->decimal('price', 12, 2)->comment('Sale price');
            $table->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->string('status', 30)->default('available')
                ->comment('available | reserved | sold | melting | withdrawn');
            $table->string('origin', 30)->default('new')->comment('new | used_purchase');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'sku']);
            $table->unique(['store_id', 'barcode']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'metal_id', 'purity_id']);
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'is_public', 'is_featured']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_weight CHECK (weight_g > 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_cost CHECK (cost >= 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_price CHECK (price >= 0)');

            // Full-text index for POS search by name or SKU
            DB::statement('ALTER TABLE products ADD FULLTEXT INDEX ft_products_name_sku (name, sku)');
        }

        Schema::create('product_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('path', 255)->comment('Public disk for public photos; private disk for internal ones');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_public')->default(true);

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_photos');
        Schema::dropIfExists('products');
    }
};
