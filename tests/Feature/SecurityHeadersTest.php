<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_pages_carry_defensive_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_login_page_carries_defensive_security_headers(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_content_security_policy_is_not_emitted_outside_production(): void
    {
        $this->get(route('home'))->assertHeaderMissing('Content-Security-Policy');
    }

    public function test_session_cookie_is_not_marked_secure_outside_production(): void
    {
        $this->assertFalse(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
    }
}
