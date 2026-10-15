<?php

namespace Tests\Feature;

use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetalPriceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function owner(): User
    {
        return User::where('email', 'admin@joyeriaweb.com')->first();
    }

    protected function manager(): User
    {
        return User::where('email', 'gerente@joyeriaweb.com')->first();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->first();
    }

    protected function goldPrice(Store $store, string $pricePerGram = '74.5000'): MetalPrice
    {
        return MetalPrice::create([
            'store_id' => $store->id,
            'metal_id' => Metal::where('code', 'GOLD')->first()->id,
            'price_per_gram' => $pricePerGram,
            'source' => 'manual',
            'price_date' => now()->toDateString(),
        ]);
    }

    public function test_any_authenticated_user_can_view_the_prices_screen(): void
    {
        $this->actingAs($this->seller())->get(route('admin.prices.index'))->assertOk();
    }

    public function test_the_prices_screen_shows_the_current_price_and_history(): void
    {
        $store = Store::first();
        $this->goldPrice($store, '123.4500');

        $response = $this->actingAs($this->manager())->get(route('admin.prices.index'));

        $response->assertOk();
        $response->assertSee('123.45');
        $response->assertSee('Precio del Metal');
    }

    public function test_a_manager_can_fix_a_price_manually_with_a_reason(): void
    {
        $manager = $this->manager();
        $gold = Metal::where('code', 'GOLD')->first();

        $response = $this->actingAs($manager)
            ->from(route('admin.prices.index'))
            ->post(route('admin.prices.manual'), [
                'metal_id' => $gold->id,
                'price_per_gram' => '99.9000',
                'reason' => 'Corrección por caída de mercado',
            ]);

        $response->assertRedirect(route('admin.prices.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('metal_prices', [
            'metal_id' => $gold->id,
            'price_per_gram' => 99.9000,
            'source' => 'manual',
            'reason' => 'Corrección por caída de mercado',
            'set_by' => $manager->id,
        ]);
    }

    public function test_a_manual_price_requires_a_reason(): void
    {
        $gold = Metal::where('code', 'GOLD')->first();

        $response = $this->actingAs($this->manager())
            ->from(route('admin.prices.index'))
            ->post(route('admin.prices.manual'), [
                'metal_id' => $gold->id,
                'price_per_gram' => '99.9000',
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_a_seller_cannot_fix_a_price_manually(): void
    {
        $gold = Metal::where('code', 'GOLD')->first();

        $this->actingAs($this->seller())
            ->post(route('admin.prices.manual'), [
                'metal_id' => $gold->id,
                'price_per_gram' => '99.9000',
                'reason' => 'Intento no autorizado',
            ])
            ->assertForbidden();
    }

    public function test_a_manager_can_refresh_the_prices_from_the_provider(): void
    {
        config([
            'services.metal_prices.provider' => 'goldapi',
            'services.metal_prices.goldapi.key' => 'test-key',
        ]);

        Http::fake([
            'goldapi.io/*' => Http::response(['price' => 2000.0], 200),
        ]);

        $response = $this->actingAs($this->manager())
            ->from(route('admin.prices.index'))
            ->post(route('admin.prices.refresh'));

        $response->assertRedirect(route('admin.prices.index'));
        $response->assertSessionHas('status');

        $gold = Metal::where('code', 'GOLD')->first();
        $silver = Metal::where('code', 'SILVER')->first();

        $this->assertDatabaseHas('metal_prices', ['metal_id' => $gold->id, 'source' => 'api', 'provider' => 'GoldAPI.io']);
        $this->assertDatabaseHas('metal_prices', ['metal_id' => $silver->id, 'source' => 'api', 'provider' => 'GoldAPI.io']);

        $stored = MetalPrice::withoutGlobalScopes()->where('metal_id', $gold->id)->latest('id')->first();
        $this->assertEqualsWithDelta(64.3015, (float) $stored->price_per_gram, 0.0001);
    }

    public function test_a_failed_refresh_keeps_the_last_valid_price_and_reports_an_error(): void
    {
        config([
            'services.metal_prices.provider' => 'goldapi',
            'services.metal_prices.goldapi.key' => 'test-key',
        ]);

        Http::fake([
            'goldapi.io/*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($this->manager())
            ->from(route('admin.prices.index'))
            ->post(route('admin.prices.refresh'));

        $response->assertRedirect(route('admin.prices.index'));
        $response->assertSessionHasErrors('error');

        $this->assertDatabaseCount('metal_prices', 0);
    }

    public function test_only_the_owner_can_access_the_purchase_discount_settings(): void
    {
        $this->actingAs($this->owner())->get(route('admin.prices.purchase_settings'))->assertOk();
        $this->actingAs($this->manager())->get(route('admin.prices.purchase_settings'))->assertForbidden();
        $this->actingAs($this->seller())->get(route('admin.prices.purchase_settings'))->assertForbidden();
    }

    public function test_the_owner_can_update_the_purchase_discounts(): void
    {
        $gold = Metal::where('code', 'GOLD')->first();

        $response = $this->actingAs($this->owner())
            ->from(route('admin.prices.purchase_settings'))
            ->put(route('admin.prices.purchase_settings.update'), [
                'discounts' => [
                    "{$gold->id}-default" => '12.50',
                ],
            ]);

        $response->assertRedirect(route('admin.prices.purchase_settings'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('purchase_settings', [
            'metal_id' => $gold->id,
            'purity_id' => null,
            'discount_percent' => 12.50,
        ]);
    }

    public function test_the_fetch_command_stores_prices_for_every_precious_metal(): void
    {
        config([
            'services.metal_prices.provider' => 'goldapi',
            'services.metal_prices.goldapi.key' => 'test-key',
        ]);

        Http::fake([
            'goldapi.io/*' => Http::response(['price' => 2000.0], 200),
        ]);

        $this->artisan('metal-prices:fetch')->assertSuccessful();

        $this->assertDatabaseCount('metal_prices', 2);
    }

    public function test_the_fetch_command_fails_when_the_provider_is_unreachable(): void
    {
        config([
            'services.metal_prices.provider' => 'goldapi',
            'services.metal_prices.goldapi.key' => 'test-key',
        ]);

        Http::fake([
            'goldapi.io/*' => Http::response([], 500),
        ]);

        $this->artisan('metal-prices:fetch')->assertFailed();

        $this->assertDatabaseCount('metal_prices', 0);
    }
}
