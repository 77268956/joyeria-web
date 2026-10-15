<?php

namespace Tests\Feature;

use App\Enums\MaterialLotStatus;
use App\Models\MaterialLot;
use App\Models\Metal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialLotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function manager(): User
    {
        return User::where('email', 'gerente@joyeriaweb.com')->first();
    }

    /**
     * @return array{metal_id: int, purity_id: int}
     */
    protected function metalAndPurity(): array
    {
        $metal = Metal::where('code', 'GOLD')->first();

        return [
            'metal_id' => $metal->id,
            'purity_id' => $metal->purities()->where('code', '18K')->first()->id,
        ];
    }

    public function test_manager_can_open_the_lot_create_form(): void
    {
        $response = $this->actingAs($this->manager())->get(route('admin.inventory.lots.create'));

        $response->assertOk();
        $response->assertSee('Nuevo Lote');
    }

    public function test_lot_index_and_show_screens_render(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '12.500'],
        ));

        $lot = MaterialLot::first();

        $this->actingAs($manager)->get(route('admin.inventory.lots.index'))->assertOk()->assertSee($lot->code);
        $this->actingAs($manager)->get(route('admin.inventory.lots.show', $lot))->assertOk()->assertSee($lot->code);
    }

    public function test_manager_can_register_a_lot_with_its_opening_weight(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '40.500', 'notes' => 'Compra a proveedor'],
        ));

        $lot = MaterialLot::first();

        $this->assertNotNull($lot);
        $response->assertRedirect(route('admin.inventory.lots.show', $lot));
        $this->assertSame(MaterialLotStatus::Open, $lot->status);
        $this->assertSame('40.500', (string) $lot->current_weight_g);
        $this->assertNotEmpty($lot->code);

        $this->assertDatabaseHas('inventory_movements', [
            'material_lot_id' => $lot->id,
            'type' => 'entry',
            'weight_delta_g' => '40.500',
        ]);
    }

    public function test_manager_can_adjust_a_lot_weight(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '20.000'],
        ));

        $lot = MaterialLot::first();

        $response = $this->actingAs($manager)->post(route('admin.inventory.lots.adjust', $lot), [
            'weight_delta_g' => '-5.500',
            'reason' => 'Consumo parcial en taller',
        ]);

        $response->assertRedirect(route('admin.inventory.lots.show', $lot));
        $this->assertSame('14.500', (string) $lot->fresh()->current_weight_g);
        $this->assertDatabaseHas('inventory_movements', [
            'material_lot_id' => $lot->id,
            'type' => 'adjustment',
            'weight_delta_g' => '-5.500',
        ]);
    }

    public function test_manager_can_send_a_lot_to_melting(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '15.000'],
        ));

        $lot = MaterialLot::first();

        $this->actingAs($manager)->post(route('admin.inventory.lots.melt', $lot), [
            'reason' => 'Traslado a fundición externa',
        ])->assertRedirect(route('admin.inventory.lots.show', $lot));

        $lot->refresh();
        $this->assertSame('0.000', (string) $lot->current_weight_g);
        $this->assertSame(MaterialLotStatus::SentToMelting, $lot->status);
    }

    public function test_lot_weight_adjustment_requires_a_reason(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '10.000'],
        ));

        $lot = MaterialLot::first();

        $response = $this->actingAs($manager)->post(route('admin.inventory.lots.adjust', $lot), [
            'weight_delta_g' => '-1.000',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertSame('10.000', (string) $lot->fresh()->current_weight_g);
    }

    public function test_seller_cannot_create_a_lot(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        $this->actingAs($seller)->post(route('admin.inventory.lots.store'), array_merge(
            $this->metalAndPurity(),
            ['initial_weight_g' => '10.000'],
        ))->assertForbidden();

        $this->assertDatabaseCount('material_lots', 0);
    }
}
