<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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

    public function test_manager_can_adjust_stock_down_and_the_change_is_audited(): void
    {
        $manager = $this->manager();
        $product = Product::factory()->create(['store_id' => $manager->store_id]);

        $response = $this->actingAs($manager)->post(route('admin.inventory.adjust.store', $product), [
            'quantity_delta' => -1,
            'reason' => 'Conteo físico: pieza no localizada',
        ]);

        $response->assertRedirect(route('admin.inventory.show', $product));
        $response->assertSessionHas('status');

        $this->assertSame(ProductStatus::Withdrawn, $product->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity_delta' => -1,
            'reason' => 'Conteo físico: pieza no localizada',
        ]);
        $this->assertDatabaseHas('activity_log', ['log_name' => 'inventario']);
    }

    public function test_adjustment_requires_a_reason(): void
    {
        $manager = $this->manager();
        $product = Product::factory()->create(['store_id' => $manager->store_id]);

        $response = $this->actingAs($manager)->post(route('admin.inventory.adjust.store', $product), [
            'quantity_delta' => -1,
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(ProductStatus::Available, $product->fresh()->status);
    }

    public function test_manager_can_send_a_piece_to_melting(): void
    {
        $manager = $this->manager();
        $product = Product::factory()->create(['store_id' => $manager->store_id]);

        $response = $this->actingAs($manager)->post(route('admin.inventory.melt', $product), [
            'reason' => 'Pieza irrecuperable, se funde',
        ]);

        $response->assertRedirect(route('admin.inventory.show', $product));
        $this->assertSame(ProductStatus::Melting, $product->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'to_melting',
            'quantity_delta' => -1,
        ]);
    }

    public function test_seller_cannot_adjust_stock(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();
        $product = Product::factory()->create(['store_id' => $seller->store_id]);

        $this->actingAs($seller)
            ->post(route('admin.inventory.adjust.store', $product), [
                'quantity_delta' => -1,
                'reason' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertSame(ProductStatus::Available, $product->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 0);
    }
}
