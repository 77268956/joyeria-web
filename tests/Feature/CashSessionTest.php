<?php

namespace Tests\Feature;

use App\Models\CashSession;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_a_difference_can_be_closed_with_a_justification_note(): void
    {
        $seller = $this->seller();
        $this->actingAs($seller)->post(route('admin.cash.open'), ['opening_amount' => '100.00']);

        $response = $this->actingAs($seller)->post(route('admin.cash.close'), [
            'counted_cash' => '95.00',
            'counted_card' => '0.00',
            'counted_transfer' => '0.00',
            'close_note' => 'Faltante de cambio',
        ]);

        $response->assertRedirect(route('admin.cash.index'));
        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $seller->id,
            'status' => 'closed',
            'difference_total' => -5.00,
            'close_note' => 'Faltante de cambio',
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
}
