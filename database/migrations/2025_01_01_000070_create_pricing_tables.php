<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('metal_id')->constrained('metals')->restrictOnDelete();
            $table->decimal('price_per_gram', 12, 4)->comment('Price of 1 g of pure metal (24k/1000) in local currency');
            $table->decimal('spot_per_ounce', 12, 4)->nullable()->comment('International troy ounce price — informational only');
            $table->decimal('exchange_rate', 12, 6)->nullable()->comment('FX rate used for conversion');
            $table->string('source', 20)->comment('api | manual');
            $table->string('provider', 40)->nullable()->comment('e.g. GoldAPI.io');
            $table->date('price_date')->comment('Reference date for this price');
            $table->foreignId('set_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('reason', 255)->nullable()->comment('Required when source = manual');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['store_id', 'metal_id', 'created_at']);
            $table->index(['store_id', 'metal_id', 'price_date']);
        });

        DB::statement('ALTER TABLE metal_prices ADD CONSTRAINT chk_metal_prices_price_per_gram CHECK (price_per_gram > 0)');

        Schema::create('purchase_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('metal_id')->constrained('metals')->restrictOnDelete();
            $table->foreignId('purity_id')->nullable()->constrained('metal_purities')->restrictOnDelete();
            $table->decimal('discount_percent', 5, 2)->comment('Store margin applied when buying precious metal');
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['store_id', 'metal_id', 'purity_id']);
        });

        DB::statement('ALTER TABLE purchase_settings ADD CONSTRAINT chk_purchase_settings_discount_percent CHECK (discount_percent >= 0 AND discount_percent <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_settings');
        Schema::dropIfExists('metal_prices');
    }
};
