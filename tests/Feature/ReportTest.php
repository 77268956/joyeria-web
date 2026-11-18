<?php

namespace Tests\Feature;

use App\Enums\LayawayStatus;
use App\Enums\ProductStatus;
use App\Models\Customer;
use App\Models\Layaway;
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
            ->assertSee('Reporte de Caja')
            ->assertSee('Reporte de Apartados');
    }

    public function test_the_reporting_dashboard_shows_metrics_and_chart_data(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $this->actingAs($this->owner())
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Ventas del período')
            ->assertSee('Ticket promedio')
            ->assertSee('overview-sales-purchases', false)
            ->assertSee('overview-payments', false)
            ->assertSee('window.reportData', false);
    }

    public function test_the_sales_report_renders_its_charts_and_table(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'ventas'))
            ->assertOk()
            ->assertSee('Tendencia de ventas')
            ->assertSee('sales-trend', false)
            ->assertSee('sales-payments', false)
            ->assertSee('Productos más vendidos')
            ->assertSee('sales-products', false)
            ->assertSee('Detalle del reporte')
            ->assertSee('window.reportData', false);
    }

    public function test_the_purchases_report_renders_its_charts(): void
    {
        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'compras'))
            ->assertOk()
            ->assertSee('Gramos por metal')
            ->assertSee('Compras por estado')
            ->assertSee('purchases-status', false);
    }

    public function test_the_cash_report_renders_its_charts(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $this->registerSale($seller);

        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'caja'))
            ->assertOk()
            ->assertSee('Arqueo por turno')
            ->assertSee('Ingresos y retiros')
            ->assertSee('cash-movements', false);
    }

    public function test_the_layaway_report_shows_balances_and_charts(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        $store = Store::firstOrFail();
        $customer = Customer::factory()->create(['store_id' => $store->id, 'full_name' => 'Cliente Apartado']);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Anillo apartado',
            'status' => ProductStatus::Available,
        ]);

        Layaway::factory()->create([
            'store_id' => $store->id,
            'number' => 'APT-TEST-001',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $seller->id,
            'total_price' => '300.00',
            'paid_total' => '100.00',
            'balance' => '200.00',
            'due_date' => now()->addDays(10)->toDateString(),
            'status' => LayawayStatus::Active,
        ]);

        $this->actingAs($this->owner())
            ->get(route('admin.reports.show', 'apartados'))
            ->assertOk()
            ->assertSee('APT-TEST-001')
            ->assertSee('Cliente Apartado')
            ->assertSee('200.00')
            ->assertSee('layaways-status', false)
            ->assertSee('layaways-aging', false)
            ->assertSee('Saldo por cliente')
            ->assertSee('layaways-balances', false);

        $export = $this->actingAs($this->owner())
            ->get(route('admin.reports.export', ['report' => 'apartados', 'format' => 'csv']));

        $export->assertOk();
        $this->assertStringContainsString('APT-TEST-001', $export->streamedContent());
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
        $response->assertSee('Valor por ubicación');
        $response->assertSee('inventory-locations', false);
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
