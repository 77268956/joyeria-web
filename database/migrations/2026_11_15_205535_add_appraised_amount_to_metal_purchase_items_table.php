<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('metal_purchase_items', function (Blueprint $table) {
            $table->decimal('appraised_amount', 12, 2)
                ->nullable()
                ->after('amount')
                ->comment('System valuation before customer negotiation');
        });

        DB::table('metal_purchase_items')->update([
            'appraised_amount' => DB::raw('amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metal_purchase_items', function (Blueprint $table) {
            $table->dropColumn('appraised_amount');
        });
    }
};
