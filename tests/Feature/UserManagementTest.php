<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_owner_can_view_user_management_screen(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Gestión de Usuarios');
        $response->assertSee($owner->name);
    }

    public function test_manager_and_seller_cannot_access_user_management(): void
    {
        $manager = User::where('email', 'gerente@joyeriaweb.com')->first();
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        $responseManager = $this->actingAs($manager)->get(route('admin.users.index'));
        $responseManager->assertForbidden();

        $responseSeller = $this->actingAs($seller)->get(route('admin.users.index'));
        $responseSeller->assertForbidden();
    }

    public function test_owner_can_create_a_new_user_with_role_and_audit_is_recorded(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $store = Store::first();

        $userData = [
            'name' => 'Nuevo Cajero',
            'email' => 'cajero@joyeriaweb.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'Vendedor',
            'store_id' => $store->id,
            'is_active' => '1',
        ];

        $response = $this->actingAs($owner)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'cajero@joyeriaweb.com',
            'name' => 'Nuevo Cajero',
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'cajero@joyeriaweb.com')->first();
        $this->assertTrue($createdUser->hasRole('Vendedor'));

        // Verify audit log
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'usuarios',
            'causer_id' => $owner->id,
        ]);
    }

    public function test_owner_can_update_user(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        $updateData = [
            'name' => 'Juan Vendedor Editado',
            'email' => 'vendedor_editado@joyeriaweb.com',
            'role' => 'Gerente',
            'is_active' => '1',
        ];

        $response = $this->actingAs($owner)->put(route('admin.users.update', $seller), $updateData);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'name' => 'Juan Vendedor Editado',
            'email' => 'vendedor_editado@joyeriaweb.com',
        ]);

        $seller->refresh();
        $this->assertTrue($seller->hasRole('Gerente'));
    }

    public function test_owner_cannot_deactivate_or_delete_themselves(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        // Attempt self toggle
        $responseToggle = $this->actingAs($owner)->post(route('admin.users.toggle_status', $owner));
        $responseToggle->assertSessionHasErrors('error');
        $this->assertTrue($owner->fresh()->is_active);

        // Attempt self delete
        $responseDelete = $this->actingAs($owner)->delete(route('admin.users.destroy', $owner));
        $responseDelete->assertSessionHasErrors('error');
        $this->assertNull($owner->fresh()->deleted_at);
    }

    public function test_owner_can_deactivate_and_reactivate_another_user(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        // Deactivate
        $this->actingAs($owner)->post(route('admin.users.toggle_status', $seller));
        $this->assertFalse($seller->fresh()->is_active);

        // Reactivate
        $this->actingAs($owner)->post(route('admin.users.toggle_status', $seller));
        $this->assertTrue($seller->fresh()->is_active);
    }
}
