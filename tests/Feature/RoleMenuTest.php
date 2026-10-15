<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_owner_sees_all_navigation_menus(): void
    {
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        $response = $this->actingAs($owner)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Punto de Venta');
        $response->assertSee('Caja');
        $response->assertSee('Apartados');
        $response->assertSee('Compra Metales');
        $response->assertSee('Inventario');
        $response->assertSee('Clientes');
        $response->assertSee('Precio del Metal');
        $response->assertSee('Reportes');
        $response->assertSee('Auditoría');
        $response->assertSee('Usuarios');
        $response->assertSee('Configuración');
    }

    public function test_manager_sees_operational_and_audit_menus_but_not_user_management(): void
    {
        $manager = User::where('email', 'gerente@joyeriaweb.com')->first();

        $response = $this->actingAs($manager)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Punto de Venta');
        $response->assertSee('Caja');
        $response->assertSee('Apartados');
        $response->assertSee('Compra Metales');
        $response->assertSee('Inventario');
        $response->assertSee('Clientes');
        $response->assertSee('Precio del Metal');
        $response->assertSee('Reportes');
        $response->assertSee('Auditoría');

        // Manager must NOT see owner-only menus
        $response->assertDontSee('admin/usuarios', false);
        $response->assertDontSee('admin/configuracion', false);
    }

    public function test_seller_sees_only_sales_and_pos_menus(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        $response = $this->actingAs($seller)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Punto de Venta');
        $response->assertSee('Caja');
        $response->assertSee('Apartados');
        $response->assertSee('Compra Metales');
        $response->assertSee('Inventario');
        $response->assertSee('Clientes');
        $response->assertSee('Precio del Metal');

        // Seller must NOT see management or owner menus
        $response->assertDontSee('admin/reportes', false);
        $response->assertDontSee('admin/auditoria', false);
        $response->assertDontSee('admin/usuarios', false);
        $response->assertDontSee('admin/configuracion', false);
    }
}
