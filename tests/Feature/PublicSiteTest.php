<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurity;
use App\Models\Product;
use App\Models\Store;
use App\Services\PricingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutVite();
    }

    protected function store(): Store
    {
        return Store::publicSite();
    }

    protected function goldPrice(string $pricePerGram = '74.5000'): void
    {
        MetalPrice::create([
            'store_id' => $this->store()->id,
            'metal_id' => Metal::where('code', 'GOLD')->value('id'),
            'price_per_gram' => $pricePerGram,
            'source' => 'manual',
            'price_date' => now()->toDateString(),
        ]);
    }

    public function test_home_shows_only_publicly_visible_pieces(): void
    {
        Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Anillo Publico',
            'is_public' => true,
            'status' => ProductStatus::Available,
        ]);
        Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Pieza Privada',
            'is_public' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Anillo Publico')
            ->assertDontSee('Pieza Privada');
    }

    public function test_catalog_lists_public_pieces_and_hides_internal_cost(): void
    {
        Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Aretes Oro',
            'cost' => 98765.43,
            'is_public' => true,
            'status' => ProductStatus::Available,
        ]);
        Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Pieza Oculta',
            'is_public' => false,
        ]);
        Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Pieza Vendida',
            'is_public' => true,
            'status' => ProductStatus::Sold,
        ]);

        $this->get(route('public.catalog'))
            ->assertOk()
            ->assertSee('Aretes Oro')
            ->assertDontSee('Pieza Oculta')
            ->assertDontSee('Pieza Vendida')
            ->assertDontSee('98765.43');
    }

    public function test_product_detail_is_not_found_for_private_piece(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store()->id,
            'is_public' => false,
        ]);

        $this->get(route('public.product', $product))->assertNotFound();
    }

    public function test_product_detail_shows_public_piece(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store()->id,
            'name' => 'Collar Fino',
            'is_public' => true,
            'status' => ProductStatus::Available,
        ]);

        $this->get(route('public.product', $product))
            ->assertOk()
            ->assertSee('Collar Fino');
    }

    public function test_gold_quoter_page_renders(): void
    {
        $this->goldPrice('74.5000');

        $this->get(route('public.sell_gold'))
            ->assertOk()
            ->assertSee('Vender mi')
            ->assertSee('publicQuote', false);
    }

    public function test_gold_quoter_returns_estimated_amount(): void
    {
        $this->goldPrice('74.5000');

        $purity = MetalPurity::where('code', '18K')->firstOrFail();
        $expected = app(PricingCalculator::class)->metalValue('10', '74.5000', (string) $purity->fineness);

        $this->postJson(route('public.sell_gold.quote'), [
            'metal_id' => $purity->metal_id,
            'purity_id' => $purity->id,
            'weight_g' => 10,
        ])
            ->assertOk()
            ->assertJson(['amount' => $expected]);
    }

    public function test_quoter_without_price_returns_message(): void
    {
        $purity = MetalPurity::where('code', '18K')->firstOrFail();

        $this->postJson(route('public.sell_gold.quote'), [
            'metal_id' => $purity->metal_id,
            'purity_id' => $purity->id,
            'weight_g' => 10,
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_contact_page_renders(): void
    {
        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('Contacto');
    }

    public function test_store_page_renders(): void
    {
        $this->get(route('public.store'))
            ->assertOk();
    }

    public function test_contact_message_is_stored(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'name' => 'Ana Lopez',
            'email' => 'ana@example.com',
            'phone' => '12345678',
            'message' => 'Quisiera informacion sobre anillos de compromiso.',
        ]);

        $response->assertRedirect(route('public.contact'));

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ana Lopez',
            'email' => 'ana@example.com',
            'store_id' => $this->store()->id,
        ]);
    }

    public function test_contact_honeypot_rejects_bots(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Compra barato ahora en nuestro sitio promocional.',
            'website' => 'http://spam.example',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_requires_valid_input(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'name' => '',
            'email' => 'no-es-un-correo',
            'message' => 'corto',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
