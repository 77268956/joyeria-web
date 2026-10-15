<?php

namespace Tests\Feature;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Metal;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_manager_can_view_the_inventory_listing(): void
    {
        $store = Store::first();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $response = $this->actingAs($this->manager())->get(route('admin.inventory.index'));

        $response->assertOk();
        $response->assertSee($product->sku);
    }

    public function test_seller_can_view_inventory_but_cannot_open_the_create_form(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->first();

        $this->actingAs($seller)->get(route('admin.inventory.index'))->assertOk();
        $this->actingAs($seller)->get(route('admin.inventory.create'))->assertForbidden();
    }

    public function test_manager_can_register_a_piece_and_an_entry_movement_is_recorded(): void
    {
        $manager = $this->manager();
        $metal = Metal::where('code', 'GOLD')->first();
        $purity = $metal->purities()->where('code', '18K')->first();
        $category = Category::first();

        $payload = [
            'name' => 'Anillo de compromiso',
            'category_id' => $category->id,
            'metal_id' => $metal->id,
            'purity_id' => $purity->id,
            'weight_g' => '5.500',
            'cost' => '300.00',
            'price' => '890.00',
            'origin' => ProductOrigin::New->value,
            'is_public' => '1',
        ];

        $response = $this->actingAs($manager)->post(route('admin.inventory.store'), $payload);

        $product = Product::where('name', 'Anillo de compromiso')->first();

        $this->assertNotNull($product);
        $response->assertRedirect(route('admin.inventory.show', $product));
        $response->assertSessionHas('status');

        $this->assertNotEmpty($product->sku);
        $this->assertNotEmpty($product->barcode);
        $this->assertSame(ProductStatus::Available, $product->status);
        $this->assertSame($manager->store_id, $product->store_id);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity_delta' => 1,
        ]);
    }

    public function test_purity_must_belong_to_the_chosen_metal(): void
    {
        $manager = $this->manager();
        $gold = Metal::where('code', 'GOLD')->first();
        $silver = Metal::where('code', 'SILVER')->first();
        $silverPurity = $silver->purities()->first();

        $payload = [
            'name' => 'Pieza inválida',
            'category_id' => Category::first()->id,
            'metal_id' => $gold->id,
            'purity_id' => $silverPurity->id,
            'weight_g' => '3.000',
            'cost' => '10.00',
            'price' => '20.00',
        ];

        $response = $this->actingAs($manager)->post(route('admin.inventory.store'), $payload);

        $response->assertSessionHasErrors('purity_id');
        $this->assertDatabaseMissing('products', ['name' => 'Pieza inválida']);
    }

    public function test_manager_can_update_a_piece(): void
    {
        $manager = $this->manager();
        $store = Store::first();
        $product = Product::factory()->create(['store_id' => $store->id]);
        $metal = $product->metal;

        $payload = [
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => 'Pieza renombrada',
            'category_id' => $product->category_id,
            'metal_id' => $metal->id,
            'purity_id' => null,
            'weight_g' => '7.250',
            'cost' => '100.00',
            'price' => '250.00',
            'is_public' => '1',
        ];

        $response = $this->actingAs($manager)->put(route('admin.inventory.update', $product), $payload);

        $response->assertRedirect(route('admin.inventory.show', $product));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Pieza renombrada',
        ]);
    }

    public function test_inventory_can_be_exported_as_csv(): void
    {
        $store = Store::first();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $response = $this->actingAs($this->manager())->get(route('admin.inventory.export'));

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('SKU', $content);
        $this->assertStringContainsString($product->sku, $content);
    }

    public function test_label_screen_renders_a_barcode_svg(): void
    {
        $store = Store::first();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $response = $this->actingAs($this->manager())->get(route('admin.inventory.label', $product));

        $response->assertOk();
        $response->assertSee('<svg', false);
        $response->assertSee($product->barcode);
    }

    public function test_inventory_screens_render_for_a_manager(): void
    {
        $manager = $this->manager();
        $store = Store::first();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $this->actingAs($manager)->get(route('admin.inventory.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.inventory.create'))->assertOk();
        $this->actingAs($manager)->get(route('admin.inventory.show', $product))->assertOk();
        $this->actingAs($manager)->get(route('admin.inventory.edit', $product))->assertOk();
        $this->actingAs($manager)->get(route('admin.inventory.adjust', $product))->assertOk();
    }
}
