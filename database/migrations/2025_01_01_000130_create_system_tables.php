<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates system-level tables:
     * - system_settings: one row per store, rich configuration (branding, limits, API).
     * - document_sequences: strict consecutive numbering per store/document type.
     * - contact_messages: public-site inquiry form submissions.
     * - activity_log: Spatie Activitylog compatible structure.
     */
    public function up(): void
    {
        // -----------------------------------------------------------------
        // system_settings — one row per store (enforced by unique on store_id)
        // -----------------------------------------------------------------
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            // Branding
            $table->string('system_name', 100)->default('Joyería Web');
            $table->string('logo_light_path', 255)->nullable()->comment('Logo for dark backgrounds');
            $table->string('logo_dark_path', 255)->nullable()->comment('Logo for light backgrounds');
            $table->string('favicon_path', 255)->nullable();

            // Color palette (customizable per store)
            $table->string('primary_color', 7)->default('#12161F')->comment('Noche');
            $table->string('background_color', 7)->default('#F7F4EE')->comment('Hueso');
            $table->string('accent_gold_color', 7)->default('#B89A5E')->comment('Dorado Champán');
            $table->string('accent_light_gold', 7)->default('#E3D6B4')->comment('Dorado Claro');

            // Operational parameters
            $table->decimal('purchase_approval_limit', 12, 2)->default(500.00)
                ->comment('Metal purchases above this amount require manager approval');
            $table->decimal('max_discount_percent', 5, 2)->default(10.00)
                ->comment('Maximum POS discount a seller may apply');
            $table->integer('layaway_max_days')->default(30)
                ->comment('Maximum days allowed for a layaway');
            $table->text('ticket_footer_text')->nullable()
                ->comment('Custom message printed at the bottom of thermal tickets');

            // Alert thresholds
            $table->integer('low_stock_alert_threshold')->default(5)
                ->comment('Dashboard alert when available products fall below this count');
            $table->decimal('cash_difference_alert_limit', 12, 2)->default(10.00)
                ->comment('Alert when cash session difference exceeds this amount');
            $table->text('default_layaway_terms')->nullable()
                ->comment('Default terms printed on layaway contracts');
            $table->text('default_purchase_disclaimer')->nullable()
                ->comment('Legal disclaimer for precious-metal purchase receipts');

            // Integrations
            $table->string('metal_api_provider', 50)->default('GoldAPI.io');
            $table->string('whatsapp_number', 20)->nullable()
                ->comment('WhatsApp number shown on the public site');

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('store_id');
        });

        DB::statement('ALTER TABLE system_settings ADD CONSTRAINT chk_purchase_limit CHECK (purchase_approval_limit >= 0)');
        DB::statement('ALTER TABLE system_settings ADD CONSTRAINT chk_max_discount CHECK (max_discount_percent >= 0 AND max_discount_percent <= 100)');
        DB::statement('ALTER TABLE system_settings ADD CONSTRAINT chk_layaway_days CHECK (layaway_max_days > 0)');

        // -----------------------------------------------------------------
        // document_sequences — collision-free consecutive numbering
        // Row-level lock (lockForUpdate) is mandatory in application code.
        // -----------------------------------------------------------------
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->string('type', 30)->comment('sale | purchase | layaway');
            $table->string('prefix', 10)->default('')->comment('Optional prefix, e.g. FAC-, COM-');
            $table->unsignedBigInteger('next_number')->default(1);

            $table->unique(['store_id', 'type']);
        });

        DB::statement('ALTER TABLE document_sequences ADD CONSTRAINT chk_next_number CHECK (next_number >= 1)');

        // -----------------------------------------------------------------
        // contact_messages — public website inquiry form submissions
        // -----------------------------------------------------------------
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->string('name', 120);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new')
                ->comment('new | read | answered');
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['store_id', 'status']);
        });

        // -----------------------------------------------------------------
        // activity_log — Spatie Activitylog compatible
        // Append-only; no updated_at.
        // -----------------------------------------------------------------
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name', 255)->nullable()->index();
            $table->text('description');
            $table->string('subject_type', 255)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event', 255)->nullable();
            $table->string('causer_type', 255)->nullable();
            $table->foreignId('causer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('system_settings');
    }
};
