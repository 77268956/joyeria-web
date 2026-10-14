<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->store = Store::create([
            'name' => 'Joyería Central',
            'code' => 'JOYTEST',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Joyería Web');
        $response->assertSee('Correo Electrónico');
        $response->assertSee('Contraseña');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::create([
            'store_id' => $this->store->id,
            'name' => 'Dueño Test',
            'email' => 'dueno@joyeria.com',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $user->assignRole('Dueño');

        $response = $this->post('/login', [
            'email' => 'dueno@joyeria.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        User::create([
            'store_id' => $this->store->id,
            'name' => 'Dueño Test',
            'email' => 'dueno@joyeria.com',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'dueno@joyeria.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        User::create([
            'store_id' => $this->store->id,
            'name' => 'Usuario Inactivo',
            'email' => 'inactivo@joyeria.com',
            'password' => Hash::make('secret123'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactivo@joyeria.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Esta cuenta se encuentra desactivada. Por favor, comunícate con la administración.']);
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::create([
            'store_id' => $this->store->id,
            'name' => 'Dueño Test',
            'email' => 'dueno@joyeria.com',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_admin_dashboard(): void
    {
        $user = User::create([
            'store_id' => $this->store->id,
            'name' => 'Dueño Test',
            'email' => 'dueno@joyeria.com',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $user->assignRole('Dueño');

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Bienvenido, Dueño Test');
        $response->assertSee('Punto de Venta');
    }
}
