<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurity;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScaleWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutVite();
        Storage::fake('local');
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
    }

    protected function openSession(User $user): void
    {
        app(CashSessionService::class)->open($user, '1000.00');
    }

    protected function purity18k(): MetalPurity
    {
        $gold = Metal::where('code', 'GOLD')->firstOrFail();

        return MetalPurity::where('metal_id', $gold->id)->where('code', '18K')->firstOrFail();
    }

    public function test_the_purchase_form_renders_the_scale_connection_widget(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);

        $response = $this->actingAs($seller)->get(route('admin.purchases.create'));

        $response->assertOk();
        $response->assertSee('data-scale-panel', escape: false);
        $response->assertSee('data-scale-reading', escape: false);
        $response->assertSee('Conectar balanza');
        $response->assertSee('Tomar peso de la balanza');
    }

    public function test_the_purchase_form_exposes_the_manual_weight_capture(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);

        $response = $this->actingAs($seller)->get(route('admin.purchases.create'));

        $response->assertOk();
        $response->assertSee('value="manual"', escape: false);
        $response->assertSee('Origen del peso');
        $response->assertSee('captura manual', escape: false);
    }

    public function test_a_purchase_item_records_the_manual_weight_source(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);

        MetalPrice::create([
            'store_id' => Store::first()->id,
            'metal_id' => Metal::where('code', 'GOLD')->firstOrFail()->id,
            'price_per_gram' => '74.5000',
            'source' => 'manual',
            'price_date' => now()->toDateString(),
        ]);

        $purity = $this->purity18k();
        $customer = Customer::factory()->create(['store_id' => Store::first()->id]);

        $this->actingAs($seller)->post(route('admin.purchases.store'), [
            'customer_id' => $customer->id,
            'payout_method' => 'cash',
            'items' => [[
                'metal_id' => $purity->metal_id,
                'purity_id' => $purity->id,
                'description' => 'Anillo con peso manual',
                'gross_weight_g' => '4.000',
                'net_weight_g' => '4.000',
                'weight_source' => 'manual',
                'destination' => 'used_inventory',
                'photo' => UploadedFile::fake()->create('anillo.jpg', 100, 'image/jpeg'),
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('metal_purchase_items', [
            'description' => 'Anillo con peso manual',
            'weight_source' => 'manual',
        ]);
    }
}
