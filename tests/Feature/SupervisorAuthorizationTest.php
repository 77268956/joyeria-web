<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\StockCountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['supervisor.test_bypass' => false]);
    }

    public function test_valid_supervisor_pin_unlocks_sensitive_actions(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $manager = User::where('email', 'gerente@joyeriaweb.com')->firstOrFail();

        $manager->update(['supervisor_pin' => '5678']);

        $response = $this->actingAs($seller)->postJson(
            route('admin.supervisor.validate_pin'),
            ['pin' => '5678'],
        );

        $response->assertOk()->assertJson([
            'valid' => true,
            'supervisor_name' => $manager->name,
        ]);

        $this->assertSame($manager->id, session('supervisor_authorization.user_id'));

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'autorizacion',
            'causer_id' => $seller->id,
        ]);
    }

    public function test_invalid_supervisor_pin_is_rejected(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $response = $this->actingAs($seller)->postJson(
            route('admin.supervisor.validate_pin'),
            ['pin' => '0000'],
        );

        $response->assertStatus(422)->assertJson(['valid' => false]);

        $this->assertNull(session('supervisor_authorization'));
    }

    public function test_inactive_supervisor_pin_is_rejected(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $manager = User::where('email', 'gerente@joyeriaweb.com')->firstOrFail();

        $manager->update(['supervisor_pin' => '5678', 'is_active' => false]);

        $this->actingAs($seller)
            ->postJson(route('admin.supervisor.validate_pin'), ['pin' => '5678'])
            ->assertStatus(422);
    }

    public function test_pin_must_have_four_digits(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)
            ->postJson(route('admin.supervisor.validate_pin'), ['pin' => '12'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('pin');
    }

    public function test_sensitive_route_requires_supervisor_authorization(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->firstOrFail();

        $response = $this->actingAs($owner)->putJson(
            route('admin.settings.update'),
            $this->settingsPayload(),
        );

        $response->assertStatus(423);
        $response->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'supervisor'));
    }

    public function test_opening_a_cash_session_does_not_require_supervisor_pin(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $response = $this->actingAs($seller)
            ->from(route('admin.cash.index'))
            ->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response->assertRedirect(route('admin.cash.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $seller->id,
            'status' => 'open',
        ]);
    }

    public function test_recording_a_stock_count_does_not_require_supervisor_pin(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->firstOrFail();
        $product = Product::factory()->create(['store_id' => $owner->store_id]);
        $count = app(StockCountService::class)->start($owner);

        $response = $this->actingAs($owner)
            ->from(route('admin.stock-counts.show', $count))
            ->post(route('admin.stock-counts.record', $count), [
                'items' => [
                    ['product_id' => $product->id, 'found' => '0'],
                ],
            ]);

        $response->assertRedirect(route('admin.stock-counts.show', $count));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $product->id,
            'found' => 0,
        ]);
    }

    public function test_sensitive_route_is_allowed_after_pin_validation(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->firstOrFail();

        $this->actingAs($owner)
            ->postJson(route('admin.supervisor.validate_pin'), ['pin' => '1234'])
            ->assertOk();

        $response = $this->actingAs($owner)->put(
            route('admin.settings.update'),
            $this->settingsPayload(['system_name' => 'Joyería Firmada']),
        );

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('system_settings', [
            'store_id' => $owner->store_id,
            'system_name' => 'Joyería Firmada',
        ]);
    }

    public function test_user_without_permission_still_receives_forbidden(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)
            ->putJson(route('admin.settings.update'), $this->settingsPayload())
            ->assertForbidden();
    }

    public function test_form_request_is_redirected_back_when_supervisor_pin_is_missing(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->firstOrFail();

        $response = $this->actingAs($owner)
            ->from(route('admin.settings.index'))
            ->put(route('admin.settings.update'), $this->settingsPayload());

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHasErrors('authorization_pin');
    }

    public function test_expired_supervisor_authorization_requires_pin_again(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->firstOrFail();

        $response = $this->actingAs($owner)
            ->withSession([
                'supervisor_authorization' => [
                    'user_id' => $owner->id,
                    'name' => $owner->name,
                    'expires_at' => now()->subMinute()->toIso8601String(),
                ],
            ])
            ->putJson(route('admin.settings.update'), $this->settingsPayload());

        $response->assertStatus(423);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function settingsPayload(array $overrides = []): array
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
