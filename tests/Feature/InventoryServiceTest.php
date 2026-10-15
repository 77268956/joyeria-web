<?php

namespace Tests\Feature;

use App\Enums\InventoryMovementType;
use App\Enums\MaterialLotStatus;
use App\Enums\ProductStatus;
use App\Models\InventoryMovement;
use App\Models\Metal;
use App\Models\MetalPurity;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;

    protected Store $store;

    protected User $user;

    protected Metal $gold;

    protected MetalPurity $gold18k;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->service = app(InventoryService::class);
        $this->store = Store::first();
        $this->user = User::where('email', 'admin@joyeriaweb.com')->first();
        $this->gold = Metal::where('code', 'GOLD')->first();
        $this->gold18k = MetalPurity::where('code', '18K')->first();
    }

    public function test_registering_a_product_entry_creates_a_movement_and_sets_it_available(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id, 'status' => ProductStatus::Withdrawn]);

        $movement = $this->service->registerProductEntry($product, null, 'Alta de prueba', $this->user->id);

        $this->assertSame(InventoryMovementType::Entry, $movement->type);
        $this->assertSame(1, $movement->quantity_delta);
        $this->assertSame($product->id, $movement->product_id);
        $this->assertNull($movement->material_lot_id);
        $this->assertSame($this->user->id, $movement->user_id);

        $this->assertSame(ProductStatus::Available, $product->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity_delta' => 1,
        ]);
    }

    public function test_adjusting_a_piece_requires_a_reason(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->adjustProduct($product, -1, '   ', $this->user->id);
    }

    public function test_adjusting_a_piece_updates_status_and_records_reason(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $this->service->adjustProduct($product, -1, 'Conteo físico: no encontrada', $this->user->id);

        $this->assertSame(ProductStatus::Withdrawn, $product->fresh()->status);
        $movement = InventoryMovement::where('product_id', $product->id)->latest('id')->first();
        $this->assertSame(-1, $movement->quantity_delta);
        $this->assertSame('Conteo físico: no encontrada', $movement->reason);
    }

    public function test_sending_a_piece_to_melting_removes_it_from_stock(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $this->service->sendProductToMelting($product, 'Pieza dañada sin reparación', null, $this->user->id);

        $this->assertSame(ProductStatus::Melting, $product->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'to_melting',
            'quantity_delta' => -1,
        ]);
    }

    public function test_creating_a_material_lot_records_its_initial_weight(): void
    {
        $lot = $this->service->createMaterialLot([
            'store_id' => $this->store->id,
            'code' => 'LOT-TEST-001',
            'metal_id' => $this->gold->id,
            'purity_id' => $this->gold18k->id,
            'initial_weight_g' => '25.500',
            'location_id' => null,
            'notes' => null,
        ], $this->user->id);

        $this->assertSame(MaterialLotStatus::Open, $lot->status);
        $this->assertSame('25.500', (string) $lot->current_weight_g);

        $movement = $lot->movements()->latest('id')->first();
        $this->assertSame(InventoryMovementType::Entry, $movement->type);
        $this->assertSame('25.500', (string) $movement->weight_delta_g);
        $this->assertNull($movement->product_id);
    }

    public function test_lot_weight_can_be_adjusted_down_but_never_below_zero(): void
    {
        $lot = $this->service->createMaterialLot([
            'store_id' => $this->store->id,
            'code' => 'LOT-TEST-002',
            'metal_id' => $this->gold->id,
            'purity_id' => $this->gold18k->id,
            'initial_weight_g' => '10.000',
            'location_id' => null,
            'notes' => null,
        ], $this->user->id);

        $this->service->adjustLotWeight($lot, '-4.250', 'Consumo en taller', $this->user->id);
        $this->assertSame('5.750', (string) $lot->fresh()->current_weight_g);

        $this->expectException(InvalidArgumentException::class);
        $this->service->adjustLotWeight($lot->fresh(), '-9.000', 'Intento inválido', $this->user->id);
    }

    public function test_sending_a_lot_to_melting_zeroes_its_weight(): void
    {
        $lot = $this->service->createMaterialLot([
            'store_id' => $this->store->id,
            'code' => 'LOT-TEST-003',
            'metal_id' => $this->gold->id,
            'purity_id' => $this->gold18k->id,
            'initial_weight_g' => '30.000',
            'location_id' => null,
            'notes' => null,
        ], $this->user->id);

        $this->service->sendLotToMelting($lot, 'Traslado a fundición externa', $this->user->id);

        $lot->refresh();
        $this->assertSame('0.000', (string) $lot->current_weight_g);
        $this->assertSame(MaterialLotStatus::SentToMelting, $lot->status);
        $this->assertDatabaseHas('inventory_movements', [
            'material_lot_id' => $lot->id,
            'type' => 'to_melting',
        ]);
    }
}
