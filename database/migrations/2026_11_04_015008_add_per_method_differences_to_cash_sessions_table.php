<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->decimal('difference_cash', 12, 2)->nullable()->after('counted_transfer')->comment('counted_cash minus expected_cash');
            $table->decimal('difference_card', 12, 2)->nullable()->after('difference_cash')->comment('counted_card minus expected_card');
            $table->decimal('difference_transfer', 12, 2)->nullable()->after('difference_card')->comment('counted_transfer minus expected_transfer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn(['difference_cash', 'difference_card', 'difference_transfer']);
        });
    }
};
