<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->string('number', 30);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->restrictOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2);
            $table->string('status', 30)->default('completed')->comment('completed | voided');
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('void_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'number']);
            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'cash_session_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_sales_subtotal CHECK (subtotal >= 0)');
            DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_sales_total CHECK (total >= 0)');
            DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_sales_discount CHECK (discount_total >= 0)');
        }

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('name_snapshot', 150);
            $table->string('metal_snapshot', 30);
            $table->string('purity_snapshot', 30)->nullable();
            $table->decimal('weight_g', 10, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2);

            $table->index('sale_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE sale_items ADD CONSTRAINT chk_sale_items_weight CHECK (weight_g > 0)');
            DB::statement('ALTER TABLE sale_items ADD CONSTRAINT chk_sale_items_price CHECK (unit_price >= 0)');
            DB::statement('ALTER TABLE sale_items ADD CONSTRAINT chk_sale_items_discount CHECK (discount >= 0)');
            DB::statement('ALTER TABLE sale_items ADD CONSTRAINT chk_sale_items_total CHECK (line_total >= 0)');
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->restrictOnDelete();
            $table->string('method', 20)->comment('cash | card | transfer');
            $table->decimal('amount', 12, 2)->comment('Amount applied to the sale');
            $table->decimal('received', 12, 2)->nullable()->comment('Cash tendered — used to compute change');
            $table->string('reference', 80)->nullable()->comment('Card approval or transfer reference');
            $table->timestamp('created_at')->useCurrent();

            $table->index('sale_id');
            $table->index(['cash_session_id', 'method']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (amount > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
