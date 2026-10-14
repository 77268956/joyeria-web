<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layaways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->string('number', 30);
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total_price', 12, 2);
            $table->decimal('paid_total', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2);
            $table->date('due_date');
            $table->string('status', 30)->default('active')->comment('active | completed | cancelled');
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('completed_sale_id')->nullable()->constrained('sales')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['store_id', 'number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'customer_id']);
        });

        DB::statement('ALTER TABLE layaways ADD CONSTRAINT chk_layaway_total CHECK (total_price > 0)');
        DB::statement('ALTER TABLE layaways ADD CONSTRAINT chk_layaway_paid CHECK (paid_total >= 0)');
        DB::statement('ALTER TABLE layaways ADD CONSTRAINT chk_layaway_balance CHECK (balance >= 0)');

        // Trigger: keep balance in sync and prevent over-payment
        DB::unprepared('
            CREATE TRIGGER trg_layaways_balance_check
            BEFORE UPDATE ON layaways
            FOR EACH ROW
            BEGIN
                IF NEW.paid_total > NEW.total_price THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "paid_total cannot exceed total_price in layaways";
                END IF;
                SET NEW.balance = NEW.total_price - NEW.paid_total;
            END
        ');

        Schema::create('layaway_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layaway_id')->constrained('layaways')->restrictOnDelete();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->restrictOnDelete();
            $table->string('method', 20)->comment('cash | card | transfer');
            $table->decimal('amount', 12, 2);
            $table->string('reference', 80)->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('layaway_id');
        });

        DB::statement('ALTER TABLE layaway_payments ADD CONSTRAINT chk_layaway_payment CHECK (amount > 0)');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_layaways_balance_check');
        Schema::dropIfExists('layaway_payments');
        Schema::dropIfExists('layaways');
    }
};
