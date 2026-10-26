<?php

namespace Tests\Feature;

use App\Models\CashSession;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->first();
    }

    protected function open(User $user, string $opening = '100.00'): CashSession
    {
        return app(CashSessionService::class)->open($user, $opening);
    }

    public function test_a_seller_can_open_a_cash_session(): void
    {
        $seller = $this->seller();

        $response = $this->actingAs($seller)
            ->from(route('admin.cash.index'))
            ->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response->assertRedirect(route('admin.cash.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $seller->id,
            'status' => 'open',
            'opening_amount' => 100.00,
        ]);
    }

    public function test_a_user_cannot_open_two_sessions_at_once(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)
            ->from(route('admin.cash.index'))
            ->post(route('admin.cash.open'), ['opening_amount' => '50.00']);

        $response->assertSessionHasErrors('error');
        $this->assertSame(1, CashSession::where('user_id', $seller->id)->count());
    }

    public function test_a_seller_can_register_a_justified_movement(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)->post(route('admin.cash.movement'), [
            'type' => 'out',
            'amount' => '20.00',
            'reason' => 'Pago de mensajería',
        ]);

        $response->assertRedirect(route('admin.cash.index'));
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'out',
            'amount' => 20.00,
            'reason' => 'Pago de mensajería',
        ]);
    }

    public function test_a_movement_requires_a_reason(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)
            ->from(route('admin.cash.index'))
            ->post(route('admin.cash.movement'), [
                'type' => 'in',
                'amount' => '20.00',
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('cash_movements', 0);
    }

    public function test_the_pos_is_blocked_without_an_open_session(): void
    {
        $response = $this->actingAs($this->seller())->get(route('admin.pos.index'));

        $response->assertRedirect(route('admin.cash.index'));
    }

    public function test_closing_without_a_difference_needs_no_note(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)->post(route('admin.cash.close'), [
            'counted_cash' => '100.00',
            'counted_card' => '0.00',
            'counted_transfer' => '0.00',
        ]);

        $response->assertRedirect(route('admin.cash.index'));
        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $seller->id,
            'status' => 'closed',
            'difference_total' => 0.00,
        ]);
    }

    public function test_closing_requires_a_note_when_there_is_a_difference(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)
            ->from(route('admin.cash.index'))
            ->post(route('admin.cash.close'), [
                'counted_cash' => '95.00',
                'counted_card' => '0.00',
                'counted_transfer' => '0.00',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('cash_sessions', ['user_id' => $seller->id, 'status' => 'open']);
    }

    public function test_closing_with_difference_requires_supervisor_pin(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        // Without PIN: fails
        $failResponse = $this->actingAs($seller)->post(route('admin.cash.close'), [
            'counted_cash' => '95.00',
            'counted_card' => '0.00',
            'counted_transfer' => '0.00',
            'close_note' => 'Faltante de cambio',
        ]);

        $failResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('cash_sessions', ['user_id' => $seller->id, 'status' => 'open']);

        // With invalid PIN: fails
        $badPinResponse = $this->actingAs($seller)->post(route('admin.cash.close'), [
            'counted_cash' => '95.00',
            'counted_card' => '0.00',
            'counted_transfer' => '0.00',
            'close_note' => 'Faltante de cambio',
            'supervisor_pin' => '9999',
        ]);

        $badPinResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('cash_sessions', ['user_id' => $seller->id, 'status' => 'open']);

        // With valid supervisor PIN: succeeds
        $manager = User::where('email', 'gerente@joyeriaweb.com')->first();
        $manager->update(['supervisor_pin' => Hash::make('5678')]);

        $response = $this->actingAs($seller)->post(route('admin.cash.close'), [
            'counted_cash' => '95.00',
            'counted_card' => '0.00',
            'counted_transfer' => '0.00',
            'close_note' => 'Faltante de cambio',
            'supervisor_pin' => '5678',
        ]);

        $response->assertRedirect(route('admin.cash.index'));
        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $seller->id,
            'status' => 'closed',
            'difference_total' => -5.00,
            'close_note' => 'Faltante de cambio',
            'supervisor_id' => $manager->id,
        ]);
    }

    public function test_a_user_without_permission_cannot_open_a_session(): void
    {
        $store = Store::first();
        $user = User::factory()->create(['store_id' => $store->id, 'is_active' => true]);

        $this->actingAs($user)
            ->post(route('admin.cash.open'), ['opening_amount' => '10.00'])
            ->assertForbidden();
    }

    public function test_cash_index_renders_open_shift_modal_when_no_session_is_active(): void
    {
        $seller = $this->seller();

        $response = $this->actingAs($seller)->get(route('admin.cash.index'));

        $response->assertOk();
        $response->assertSee('Sin turno abierto');
        $response->assertSee('Abrir turno de caja');
        $response->assertSee('modal-open-shift');
        $response->assertSee('Historial de turnos cerrados');
    }

    public function test_cash_index_renders_shift_container_and_action_modals_when_session_is_open(): void
    {
        $seller = $this->seller();
        $this->open($seller, '150.00');

        $response = $this->actingAs($seller)->get(route('admin.cash.index'));

        $response->assertOk();
        $response->assertSee('Turno abierto');
        $response->assertSee('Movimientos de turno');
        $response->assertSee('Cerrar turno');
        $response->assertSee('modal-movements');
        $response->assertSee('modal-close-shift');
        $response->assertSee('Historial de turnos cerrados');
    }
}
