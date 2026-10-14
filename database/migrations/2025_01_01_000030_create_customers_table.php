<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the customers table.
     * Requires stores to exist.
     * Personal data (id_photo_path) is stored in private disk — never in public/.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->string('full_name', 150);
            $table->string('document_type', 20)->comment('Cédula, DNI, Pasaporte');
            $table->string('document_number', 40);
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('id_photo_path', 255)->nullable()->comment('Private disk path — storage/app/private/');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // A customer is uniquely identified by store + document type + document number
            $table->unique(['store_id', 'document_type', 'document_number']);

            // Frequent search by name within a store
            $table->index(['store_id', 'full_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
