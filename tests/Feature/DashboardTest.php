<?php

namespace Tests\Feature;

use App\Enums\MetalPriceSource;
use App\Enums\ProductStatus;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutVite();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
    }

    public function test_the_dashboard_shows_today_sales_totals_and_chart_data(): void
    {
        $seller = $this->seller();
        app(CashSessionService::class)->open($seller, '0.00');

        $product = Product::factory()->create([
            'store_id' => Store::first()->id,
            'price' => '100.00',
            'status' => ProductStatus::Available,
        ]);

        $this->actingAs($seller)->post(route('admin.pos.store'), [
            'items' => [['product_id' => $product->id, 'discount' => '0']],
            'discount_total' => '0',
            'payments' => [['method' => 'cash', 'amount' => '100.00', 'received' => '100.00']],
        ])->assertOk();

        $response = $this->actingAs($seller)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Ventas del Día');
        $response->assertSee('$100.00');
        $response->assertSee('window.dashboardData');
        $response->assertSee('sales-chart');
    }

    public function test_the_dashboard_flags_a_metal_below_the_stock_threshold(): void
    {
        $seller = $this->seller();

        $product = Product::factory()->create([
            'store_id' => Store::first()->id,
            'status' => ProductStatus::Sold,
        ]);

        $this->actingAs($seller)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Alerta de stock bajo')
            ->assertSee($product->metal->name);
    }

    public function test_the_dashboard_shows_the_latest_gold_quote(): void
    {
        $seller = $this->seller();

        MetalPrice::create([
            'store_id' => Store::first()->id,
            'metal_id' => Metal::where('code', 'GOLD')->firstOrFail()->id,
            'price_per_gram' => '1234.5678',
            'source' => MetalPriceSource::Manual,
            'price_date' => today(),
            'reason' => 'Precio de prueba',
        ]);

        $this->actingAs($seller)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Cotización del Oro')
            ->assertSee('1,234.5678');
    }
}
