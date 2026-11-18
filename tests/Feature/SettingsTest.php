<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_owner_can_view_the_system_settings_screen(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $settings = SystemSetting::where('store_id', $owner->store_id)->firstOrFail();

        $response = $this->actingAs($owner)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Configuración del Sistema');
        $response->assertSee($settings->system_name);
        $response->assertSee('Guardar configuración');
    }

    public function test_manager_and_seller_cannot_access_or_update_system_settings(): void
    {
        $manager = User::where('email', 'gerente@joyeriaweb.com')->first();
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        foreach ([$manager, $seller] as $actor) {
            $this->actingAs($actor)
                ->get(route('admin.settings.index'))
                ->assertForbidden();

            $this->actingAs($actor)
                ->put(route('admin.settings.update'), $this->payload())
                ->assertForbidden();
        }
    }

    public function test_owner_can_update_the_system_name_and_it_appears_in_the_panel_and_login(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $settings = SystemSetting::where('store_id', $owner->store_id)->firstOrFail();

        $response = $this->actingAs($owner)->put(
            route('admin.settings.update'),
            $this->payload(['system_name' => 'Joyería Aurora']),
        );

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('system_settings', [
            'id' => $settings->id,
            'system_name' => 'Joyería Aurora',
        ]);

        $settings->refresh();

        $this->assertSame('12.50', $settings->max_discount_percent);
        $this->assertSame(45, $settings->layaway_max_days);
        $this->assertSame(8, $settings->low_stock_alert_threshold);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'configuracion',
            'causer_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertSee('Joyería Aurora');

        auth()->logout();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Joyería Aurora');
    }

    public function test_settings_validation_rejects_invalid_values(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        $response = $this->actingAs($owner)->put(
            route('admin.settings.update'),
            $this->payload([
                'system_name' => '   ',
                'purchase_approval_limit' => '-1',
                'max_discount_percent' => '150',
                'layaway_max_days' => '0',
                'low_stock_alert_threshold' => '-2',
                'cash_difference_alert_limit' => '-1',
                'whatsapp_number' => 'llamame',
            ]),
        );

        $response->assertSessionHasErrors([
            'system_name',
            'purchase_approval_limit',
            'max_discount_percent',
            'layaway_max_days',
            'low_stock_alert_threshold',
            'cash_difference_alert_limit',
            'whatsapp_number',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'system_name' => 'Joyería Web',
            'purchase_approval_limit' => '750.00',
            'max_discount_percent' => '12.50',
            'layaway_max_days' => 45,
            'low_stock_alert_threshold' => 8,
            'cash_difference_alert_limit' => '15.00',
            'ticket_footer_text' => 'Gracias por su compra.',
            'default_layaway_terms' => 'Los apartados requieren un abono mínimo del 30%.',
            'default_purchase_disclaimer' => 'La valoración de metales depende del precio del día.',
            'whatsapp_number' => '+15550192834',
        ], $overrides);
    }
}
