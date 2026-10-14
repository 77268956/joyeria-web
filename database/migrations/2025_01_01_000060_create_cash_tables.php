<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('open')->comment('open | closed');
            $table->timestamp('opened_at');
            $table->decimal('opening_amount', 12, 2)->comment('Cash float at session start');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('expected_card', 12, 2)->nullable();
            $table->decimal('expected_transfer', 12, 2)->nullable();
            $table->decimal('counted_cash', 12, 2)->nullable();
            $table->decimal('counted_card', 12, 2)->nullable();
            $table->decimal('counted_transfer', 12, 2)->nullable();
            $table->decimal('difference_total', 12, 2)->nullable()->comment('counted minus expected');
            $table->string('close_note', 255)->nullable()->comment('Required when difference_total != 0');

            $table->index(['store_id', 'user_id', 'status']);
            $table->index(['store_id', 'opened_at']);
        });

        DB::statement('ALTER TABLE cash_sessions ADD CONSTRAINT chk_cash_sessions_opening_amount CHECK (opening_amount >= 0)');

        // Trigger: block a second open session for the same user
        DB::unprepared('
            CREATE TRIGGER trg_cash_sessions_one_open_per_user
            BEFORE INSERT ON cash_sessions
            FOR EACH ROW
            BEGIN
                DECLARE open_count INT;
                SELECT COUNT(*) INTO open_count
                FROM cash_sessions
                WHERE user_id = NEW.user_id
                  AND status = "open";
                IF open_count > 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "User already has an open cash session";
                END IF;
            END
        ');

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->restrictOnDelete();
            $table->string('type', 10)->comment('in | out');
            $table->decimal('amount', 12, 2)->comment('Always positive; direction is given by type');
            $table->string('reason', 255)->comment('Mandatory justification');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['store_id', 'cash_session_id']);
        });

        DB::statement('ALTER TABLE cash_movements ADD CONSTRAINT chk_cash_movements_amount CHECK (amount > 0)');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_cash_sessions_one_open_per_user');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_sessions');
    }
};
