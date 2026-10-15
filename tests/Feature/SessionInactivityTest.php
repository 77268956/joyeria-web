<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionInactivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_active_user_session_is_maintained(): void
    {
        $user = User::where('email', 'admin@joyeriaweb.com')->first();

        $response = $this->actingAs($user)
            ->withSession(['last_activity_time' => time()])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_session_expires_after_timeout(): void
    {
        $user = User::where('email', 'admin@joyeriaweb.com')->first();

        // 31 minutes ago (exceeding default 30 min timeout = 1800s)
        $pastTime = time() - 1860;

        $response = $this->actingAs($user)
            ->withSession(['last_activity_time' => $pastTime])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
