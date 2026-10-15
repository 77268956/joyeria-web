<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    protected function registerSale(User $seller, string $price = '100.00'): Product
    {
        app(CashSessionService::class)->open($seller, '0.00');

        $product = Product::factory()->create([
            'store_id' => Store::first()->id,
            'name' => 'Anillo de prueba',
            'price' => $price,
            'cost' => '30.00',
            'status' => ProductStatus::Available,
        ]);

        $this->actingAs($seller)->post(route('admin.pos.store'), [
            'items' => [['product_id' => $product->id, 'discount' => '0']],
            'discount_total' => '0',
            'payments' => [['method' => 'cash', 'amount' => $price, 'received' => $price]],
        ])->assertOk();

        return $product;
    }

    public function test_the_report_catalogue_lists_every_report_for_the_owner(): void
    {
        $this->actingAs($this->owner())
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reporte de Ventas')
            ->assertSee('Reporte de Compras de Metal')
            ->assertSee('Reporte de Inventario')
            ->assertSee('Reporte de Caja');
    }

    public function test_a_seller_cannot_access_reports(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($seller)->get(route('admin.reports.show', 'ventas'))->assertForbidden();
    }

    public function test_the_sales_report_shows_registered_sales(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'ventas'))
            ->assertOk()
            ->assertSee('VTA-000001')
            ->assertSee('100.00');
    }

    public function test_the_inventory_report_values_in_stock_pieces(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $available = Product::factory()->create([
            'store_id' => Store::first()->id,
            'name' => 'Aretes disponibles',
            'cost' => '40.00',
            'price' => '120.00',
            'status' => ProductStatus::Available,
        ]);

        $response = $this->actingAs($this->owner())->get(route('admin.reports.show', 'inventario'));

        $response->assertOk();
        $response->assertSee('Aretes disponibles');
        $response->assertSee('120.00');
        $response->assertDontSee('Anillo de prueba');
        $this->assertSame(ProductStatus::Available, $available->refresh()->status);
    }

    public function test_a_report_can_be_exported_as_csv(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $response = $this->actingAs($this->owner())
            ->get(route('admin.reports.export', ['report' => 'ventas', 'format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Folio', $content);
        $this->assertStringContainsString('VTA-000001', $content);
    }

    public function test_a_report_can_be_exported_as_pdf(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $response = $this->actingAs($this->owner())
            ->get(route('admin.reports.export', ['report' => 'ventas', 'format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_an_unknown_report_returns_not_found(): void
    {
        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'desconocido'))
            ->assertNotFound();
    }
}
