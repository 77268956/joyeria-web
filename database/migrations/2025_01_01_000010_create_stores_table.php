<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the stores table and then adds the store_id FK to users.
     * Stores must exist before users are bound to them.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 10)->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add store_id FK to users now that stores exists.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->after('id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->index('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });

        Schema::dropIfExists('stores');
    }
};
