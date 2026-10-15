<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleVoidTest extends TestCase
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

    protected function product(string $price = '100.00'): Product
    {
        return Product::factory()->create([
            'store_id' => Store::first()->id,
            'price' => $price,
            'weight_g' => '5.000',
            'status' => ProductStatus::Available,
        ]);
    }

    protected function createSale(Product $product, ?User $seller = null): Sale
    {
        $seller ??= $this->seller();
        $session = app(CashSessionService::class)->open($seller, '0.00');

        return app(SaleService::class)->create($seller, $session, [
            'items' => [['product_id' => $product->id, 'discount' => '0']],
            'discount_total' => '0',
            'payments' => [
                ['method' => 'cash', 'amount' => (string) $product->price, 'received' => (string) $product->price],
            ],
        ]);
    }

    public function test_a_manager_can_void_a_sale_and_return_the_piece_to_stock(): void
    {
        $product = $this->product();
        $sale = $this->createSale($product);

        $response = $this->actingAs($this->manager())->post(route('admin.sales.void', $sale), [
            'reason' => 'Error de cobro',
        ]);

        $response->assertRedirect(route('admin.sales.show', $sale));

        $this->assertSame(SaleStatus::Voided, $sale->refresh()->status);
        $this->assertSame(ProductStatus::Available, $product->refresh()->status);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => $product->id, 'type' => 'sale_void']);
    }

    public function test_a_seller_cannot_void_a_sale(): void
    {
        $product = $this->product();
        $sale = $this->createSale($product);

        $this->actingAs($this->seller())
            ->post(route('admin.sales.void', $sale), ['reason' => 'Intento'])
            ->assertForbidden();

        $this->assertSame(SaleStatus::Completed, $sale->refresh()->status);
    }

    public function test_voiding_requires_a_reason(): void
    {
        $product = $this->product();
        $sale = $this->createSale($product);

        $response = $this->actingAs($this->manager())
            ->from(route('admin.sales.show', $sale))
            ->post(route('admin.sales.void', $sale), ['reason' => '']);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(SaleStatus::Completed, $sale->refresh()->status);
    }

    public function test_the_expected_cash_excludes_voided_sales(): void
    {
        $seller = $this->seller();
        $product = $this->product('100.00');
        $sale = $this->createSale($product, $seller);
        $session = $sale->cashSession;

        $this->assertSame('100.00', app(CashSessionService::class)->expectedTotals($session)['cash']);

        app(SaleService::class)->void($sale, 'Deshacer', $this->manager());

        $this->assertSame('0.00', app(CashSessionService::class)->expectedTotals($session->refresh())['cash']);
    }
}
