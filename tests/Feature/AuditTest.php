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

    public function test_the_audit_log_can_be_filtered_by_responsible_and_date_range(): void
    {
        $owner = $this->owner();
        $manager = User::where('email', 'gerente@joyeriaweb.com')->firstOrFail();

        $this->travelTo(now()->subDays(10));
        activity('precios')->causedBy($owner)->log('Evento XYZ antiguo');
        $this->travelBack();

        activity('inventario')->causedBy($manager)->log('Evento XYZ reciente');

        $this->actingAs($owner)
            ->get(route('admin.audit.index', [
                'search' => 'XYZ',
                'causer_id' => $manager->id,
                'from' => now()->subDays(2)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Evento XYZ reciente')
            ->assertDontSee('Evento XYZ antiguo');
    }

    public function test_the_audit_log_shows_event_properties(): void
    {
        $owner = $this->owner();

        activity('precios')
            ->causedBy($owner)
            ->withProperties(['reason' => 'Ajuste manual de auditoría'])
            ->log('Evento con detalle ABC');

        $this->actingAs($owner)
            ->get(route('admin.audit.index', ['search' => 'ABC']))
            ->assertOk()
            ->assertSee('Ver detalles')
            ->assertSee('Motivo')
            ->assertSee('Ajuste manual de auditoría');
    }

    public function test_the_audit_log_shows_created_record_data(): void
    {
        $owner = $this->owner();

        activity('inventario')
            ->causedBy($owner)
            ->event('created')
            ->withChanges([
                'attributes' => ['name' => 'Anillo de prueba', 'price' => '150.00', 'is_active' => true],
                'old' => [],
            ])
            ->log('Pieza creada ABC');

        $this->actingAs($owner)
            ->get(route('admin.audit.index', ['search' => 'Anillo de prueba']))
            ->assertOk()
            ->assertSee('Datos registrados')
            ->assertSee('Creación')
            ->assertSee('Anillo de prueba')
            ->assertSee('150.00')
            ->assertSee('Activo')
            ->assertSee('Sí');
    }

    public function test_the_audit_log_shows_old_and_new_values(): void
    {
        $owner = $this->owner();

        activity('precios')
            ->causedBy($owner)
            ->event('updated')
            ->withChanges([
                'attributes' => ['price' => '200.00'],
                'old' => ['price' => '150.00'],
            ])
            ->log('Precio actualizado XYZ');

        $this->actingAs($owner)
            ->get(route('admin.audit.index', ['search' => 'XYZ']))
            ->assertOk()
            ->assertSee('Actualización')
            ->assertSee('Cambios')
            ->assertSee('Antes')
            ->assertSee('Después')
            ->assertSee('150.00')
            ->assertSee('200.00');
    }

    public function test_a_seller_cannot_browse_the_audit_log(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)->get(route('admin.audit.index'))->assertForbidden();
    }
}
