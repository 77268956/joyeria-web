<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->first();
    }

    protected function manager(): User
    {
        return User::where('email', 'gerente@joyeriaweb.com')->first();
    }

    protected function openSession(User $user): void
    {
        app(CashSessionService::class)->open($user, '0.00');
    }

    protected function product(string $price = '100.00'): Product
    {
        return Product::factory()->create([
            'store_id' => Store::first()->id,
            'price' => $price,
            'weight_g' => '5.000',
            'status' => ProductStatus::Available,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'items' => [
                ['product_id' => $product->id, 'discount' => '0'],
            ],
            'discount_total' => '0',
            'payments' => [
                ['method' => 'cash', 'amount' => (string) $product->price, 'received' => (string) $product->price],
            ],
        ], $overrides);
    }

    public function test_the_pos_screen_loads_with_an_open_session(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);

        $this->actingAs($seller)->get(route('admin.pos.index'))->assertOk()->assertSee('Punto de Venta');
    }

    public function test_search_returns_available_products(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product();

        $response = $this->actingAs($seller)->getJson(route('admin.pos.search', ['q' => $product->sku]));

        $response->assertOk();
        $response->assertJsonPath('products.0.id', $product->id);
    }

    public function test_a_seller_can_register_a_sale(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $response = $this->actingAs($seller)->postJson(route('admin.pos.store'), $this->payload($product));

        $response->assertOk();
        $response->assertJsonStructure(['sale_id', 'number', 'total', 'ticket_url']);

        $this->assertDatabaseHas('sales', ['number' => 'VTA-000001', 'total' => 100.00, 'status' => 'completed']);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'unit_price' => 100.00, 'line_total' => 100.00]);
        $this->assertDatabaseHas('payments', ['method' => 'cash', 'amount' => 100.00]);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => $product->id, 'type' => 'sale']);
        $this->assertSame(ProductStatus::Sold, $product->refresh()->status);
    }

    public function test_the_total_is_recomputed_server_side(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'items' => [
                ['product_id' => $product->id, 'discount' => '0', 'price' => '1.00', 'unit_price' => '1.00'],
            ],
        ]);

        $this->actingAs($seller)->postJson(route('admin.pos.store'), $payload)->assertOk();

        $this->assertDatabaseHas('sales', ['number' => 'VTA-000001', 'total' => 100.00]);
    }

    public function test_payments_must_match_the_total(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'payments' => [['method' => 'cash', 'amount' => '90.00', 'received' => '90.00']],
        ]);

        $this->actingAs($seller)->postJson(route('admin.pos.store'), $payload)->assertStatus(422);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_cash_received_cannot_be_less_than_the_cash_amount(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'payments' => [['method' => 'cash', 'amount' => '100.00', 'received' => '50.00']],
        ]);

        $this->actingAs($seller)->postJson(route('admin.pos.store'), $payload)->assertStatus(422);
    }

    public function test_a_discount_over_the_limit_is_rejected_for_a_seller(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'discount_total' => '30',
            'discount_reason' => 'Cliente frecuente',
            'payments' => [['method' => 'cash', 'amount' => '70.00', 'received' => '70.00']],
        ]);

        $response = $this->actingAs($seller)->postJson(route('admin.pos.store'), $payload);

        $response->assertStatus(422);
        $this->assertStringContainsString('supera el máximo', $response->json('message'));
    }

    public function test_a_manager_can_override_the_discount_limit(): void
    {
        $manager = $this->manager();
        $this->openSession($manager);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'discount_total' => '30',
            'discount_reason' => 'Cliente frecuente',
            'payments' => [['method' => 'cash', 'amount' => '70.00', 'received' => '70.00']],
        ]);

        $this->actingAs($manager)->postJson(route('admin.pos.store'), $payload)->assertOk();

        $this->assertDatabaseHas('sales', ['total' => 70.00, 'discount_total' => 30.00]);
    }

    public function test_a_discount_requires_a_reason(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = $this->product('100.00');

        $payload = $this->payload($product, [
            'discount_total' => '5',
            'payments' => [['method' => 'cash', 'amount' => '95.00', 'received' => '95.00']],
        ]);

        $this->actingAs($seller)->postJson(route('admin.pos.store'), $payload)->assertStatus(422);
    }

    public function test_a_piece_that_is_not_available_cannot_be_sold(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $product = Product::factory()->create([
            'store_id' => Store::first()->id,
            'price' => '100.00',
            'weight_g' => '5.000',
            'status' => ProductStatus::Sold,
        ]);

        $this->actingAs($seller)->postJson(route('admin.pos.store'), $this->payload($product))->assertStatus(422);
    }

    public function test_a_sale_cannot_be_registered_without_an_open_session(): void
    {
        $seller = $this->seller();
        $product = $this->product('100.00');

        $this->actingAs($seller)
            ->postJson(route('admin.pos.store'), $this->payload($product))
            ->assertStatus(409);
    }
}
