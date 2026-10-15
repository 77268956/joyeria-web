<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function owner(): User
    {
        return User::where('email', 'admin@joyeriaweb.com')->firstOrFail();
    }

    public function test_the_owner_can_browse_the_audit_log(): void
    {
        $owner = $this->owner();

        activity('precios')
            ->causedBy($owner)
            ->log('Precio del oro actualizado manualmente');

        $this->actingAs($owner)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Bitácora de Auditoría')
            ->assertSee('Precio del oro actualizado manualmente')
            ->assertSee('precios');
    }

    public function test_the_audit_log_can_be_filtered_by_description(): void
    {
        $owner = $this->owner();

        activity('precios')->causedBy($owner)->log('Precio del oro actualizado');
        activity('inventario')->causedBy($owner)->log('Ajuste de inventario aplicado');

        $this->actingAs($owner)
            ->get(route('admin.audit.index', ['search' => 'inventario']))
            ->assertOk()
            ->assertSee('Ajuste de inventario aplicado')
            ->assertDontSee('Precio del oro actualizado');
    }

    public function test_a_seller_cannot_browse_the_audit_log(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)->get(route('admin.audit.index'))->assertForbidden();
    }
}
