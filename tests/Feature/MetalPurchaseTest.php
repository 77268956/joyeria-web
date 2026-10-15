<?php

namespace Tests\Feature;

use App\Enums\MetalPurchaseStatus;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurchase;
use App\Models\MetalPurity;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MetalPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->first();
    }

    protected function manager(): User
    {
        return User::where('email', 'gerente@joyeriaweb.com')->first();
    }

    protected function openSession(User $user, string $openingAmount = '1000.00'): CashSession
    {
        return app(CashSessionService::class)->open($user, $openingAmount);
    }

    protected function goldPrice(string $pricePerGram = '74.5000'): void
    {
        MetalPrice::create([
            'store_id' => Store::first()->id,
            'metal_id' => Metal::where('code', 'GOLD')->first()->id,
            'price_per_gram' => $pricePerGram,
            'source' => 'manual',
            'price_date' => now()->toDateString(),
        ]);
    }

    protected function customer(): Customer
    {
        return Customer::factory()->create(['store_id' => Store::first()->id]);
    }

    protected function purity18k(): MetalPurity
    {
        $gold = Metal::where('code', 'GOLD')->first();

        return MetalPurity::where('metal_id', $gold->id)->where('code', '18K')->first();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function itemPayload(MetalPurity $purity, array $overrides = []): array
    {
        return array_merge([
            'metal_id' => $purity->metal_id,
            'purity_id' => $purity->id,
            'description' => 'Cadena rota 18k',
            'gross_weight_g' => '5.000',
            'net_weight_g' => '5.000',
            'weight_source' => 'scale',
            'destination' => 'used_inventory',
            'photo' => UploadedFile::fake()->create('pieza.jpg', 100, 'image/jpeg'),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $this->customer()->id,
            'payout_method' => 'cash',
            'items' => [$this->itemPayload($this->purity18k())],
        ], $overrides);
    }

    public function test_the_purchase_form_loads_with_an_open_session(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);

        $this->actingAs($seller)->get(route('admin.purchases.create'))->assertOk()->assertSee('Nueva compra de metales');
    }

    public function test_the_purchase_form_requires_an_open_session(): void
    {
        $this->actingAs($this->seller())
            ->get(route('admin.purchases.create'))
            ->assertRedirect(route('admin.cash.index'));
    }

    public function test_a_seller_can_register_a_purchase_that_moves_inventory_and_cash(): void
    {
        $seller = $this->seller();
        $session = $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('metal_purchases', [
            'number' => 'COM-000001',
            'status' => 'paid',
            'payout_method' => 'cash',
            'total_amount' => 265.41,
        ]);
        $this->assertDatabaseHas('metal_purchase_items', ['description' => 'Cadena rota 18k', 'amount' => 265.41]);
        $this->assertDatabaseHas('products', ['name' => 'Cadena rota 18k', 'origin' => 'used_purchase']);
        $this->assertDatabaseHas('inventory_movements', ['type' => 'purchase_entry']);
        $this->assertDatabaseHas('cash_movements', ['cash_session_id' => $session->id, 'type' => 'out', 'amount' => 265.41]);

        $expected = app(CashSessionService::class)->expectedTotals($session);
        $this->assertSame('734.59', $expected['cash']);
    }

    public function test_a_transfer_purchase_does_not_touch_cash(): void
    {
        $seller = $this->seller();
        $session = $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload(['payout_method' => 'transfer']))
            ->assertRedirect();

        $this->assertDatabaseCount('cash_movements', 0);
        $expected = app(CashSessionService::class)->expectedTotals($session);
        $this->assertSame('1000.00', $expected['cash']);
    }

    public function test_a_high_value_purchase_by_a_seller_waits_for_approval(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload([
            'items' => [$this->itemPayload($this->purity18k(), ['net_weight_g' => '10.000', 'gross_weight_g' => '10.000'])],
        ]))->assertRedirect();

        $this->assertDatabaseHas('metal_purchases', ['number' => 'COM-000001', 'status' => 'pending_approval', 'total_amount' => 530.81]);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('cash_movements', 0);
    }

    public function test_a_manager_can_approve_a_pending_purchase(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload([
            'items' => [$this->itemPayload($this->purity18k(), ['net_weight_g' => '10.000', 'gross_weight_g' => '10.000'])],
        ]));

        $purchase = MetalPurchase::withoutGlobalScopes()->first();
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('admin.purchases.approve', $purchase), ['approval_note' => 'Autorizado'])
            ->assertSessionHas('status');

        $purchase->refresh();
        $this->assertSame(MetalPurchaseStatus::Paid, $purchase->status);
        $this->assertSame($manager->id, $purchase->approved_by);
        $this->assertDatabaseHas('products', ['origin' => 'used_purchase']);
        $this->assertDatabaseHas('cash_movements', ['type' => 'out', 'amount' => 530.81]);
    }

    public function test_a_seller_cannot_approve_a_purchase(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload([
            'items' => [$this->itemPayload($this->purity18k(), ['net_weight_g' => '10.000', 'gross_weight_g' => '10.000'])],
        ]));

        $purchase = MetalPurchase::withoutGlobalScopes()->first();

        $this->actingAs($seller)->post(route('admin.purchases.approve', $purchase))->assertForbidden();
        $this->assertSame(MetalPurchaseStatus::PendingApproval, $purchase->refresh()->status);
    }

    public function test_a_manager_can_reject_a_pending_purchase(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload([
            'items' => [$this->itemPayload($this->purity18k(), ['net_weight_g' => '10.000', 'gross_weight_g' => '10.000'])],
        ]));

        $purchase = MetalPurchase::withoutGlobalScopes()->first();

        $this->actingAs($this->manager())->post(route('admin.purchases.reject', $purchase), ['reason' => 'No hay presupuesto'])
            ->assertSessionHas('status');

        $this->assertSame(MetalPurchaseStatus::Rejected, $purchase->refresh()->status);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_a_manager_can_adjust_a_paid_purchase_and_balance_cash(): void
    {
        $seller = $this->seller();
        $session = $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload());

        $purchase = MetalPurchase::withoutGlobalScopes()->first();

        $this->actingAs($this->manager())->put(route('admin.purchases.adjust.store', $purchase), [
            'total_amount' => '200.00',
            'reason' => 'Error de pesaje',
        ])->assertRedirect(route('admin.purchases.show', $purchase));

        $purchase->refresh();
        $this->assertSame(MetalPurchaseStatus::Adjusted, $purchase->status);
        $this->assertSame('200.00', $purchase->total_amount);
        $this->assertDatabaseHas('purchase_adjustments', ['metal_purchase_id' => $purchase->id, 'reason' => 'Error de pesaje', 'amount_difference' => -65.41]);
        $this->assertDatabaseHas('cash_movements', ['cash_session_id' => $session->id, 'type' => 'in', 'amount' => 65.41]);

        $expected = app(CashSessionService::class)->expectedTotals($session);
        $this->assertSame('800.00', $expected['cash']);
    }

    public function test_the_quote_endpoint_returns_the_valuation_snapshot(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $purity = $this->purity18k();

        $this->actingAs($seller)->postJson(route('admin.purchases.quote'), [
            'metal_id' => $purity->metal_id,
            'purity_id' => $purity->id,
            'gross_weight_g' => '5.000',
            'net_weight_g' => '5.000',
        ])
            ->assertOk()
            ->assertJsonPath('amount', '265.41')
            ->assertJsonPath('fineness_snapshot', '0.7500')
            ->assertJsonPath('price_per_gram_snapshot', '74.5000');
    }

    public function test_the_legal_receipt_is_downloaded_as_pdf(): void
    {
        $seller = $this->seller();
        $this->openSession($seller);
        $this->goldPrice();

        $this->actingAs($seller)->post(route('admin.purchases.store'), $this->payload());
        $purchase = MetalPurchase::withoutGlobalScopes()->first();

        $response = $this->actingAs($seller)->get(route('admin.purchases.receipt', $purchase));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
