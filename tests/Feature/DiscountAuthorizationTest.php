<?php

namespace Tests\Feature;

use App\Enums\DiscountAuthorizationStatus;
use App\Enums\ProductStatus;
use App\Models\DiscountAuthorization;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DiscountAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $seller;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->store = Store::first();
        $this->seller = User::where('email', 'vendedor@joyeriaweb.com')->first();
        $this->manager = User::where('email', 'gerente@joyeriaweb.com')->first();

        // Ensure manager has supervisor_pin '5678'
        $this->manager->update(['supervisor_pin' => Hash::make('5678')]);

        // Ensure max discount is 10.00%
        SystemSetting::where('store_id', $this->store->id)->update(['max_discount_percent' => '10.00']);
    }

    protected function openSession(User $user): void
    {
        app(CashSessionService::class)->open($user, '1000.00');
    }

    protected function product(string $price = '1000.00'): Product
    {
        return Product::factory()->create([
            'store_id' => $this->store->id,
            'price' => $price,
            'weight_g' => '10.000',
            'status' => ProductStatus::Available,
        ]);
    }

    public function test_seller_can_apply_discount_within_10_percent_without_supervisor(): void
    {
        $this->openSession($this->seller);
        $product = $this->product('1000.00');

        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.store'), [
            'items' => [
                ['product_id' => $product->id, 'discount' => '0'],
            ],
            'discount_total' => '80.00', // 8% (<= 10%)
            'discount_reason' => 'Cortesía cliente',
            'payments' => [
                ['method' => 'cash', 'amount' => '920.00', 'received' => '1000.00'],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sales', [
            'store_id' => $this->store->id,
            'total' => '920.00',
            'discount_total' => '80.00',
        ]);
    }

    public function test_seller_cannot_apply_discount_above_limit_without_authorization(): void
    {
        $this->openSession($this->seller);
        $product = $this->product('1000.00');

        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.store'), [
            'items' => [
                ['product_id' => $product->id, 'discount' => '0'],
            ],
            'discount_total' => '150.00', // 15% (> 10%)
            'discount_reason' => 'Petición de cliente',
            'payments' => [
                ['method' => 'cash', 'amount' => '850.00', 'received' => '850.00'],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'El descuento (15.00%) supera el máximo permitido (10.00%). Requiere autorización de gerencia.',
        ]);
    }

    public function test_supervisor_pin_validation_endpoint(): void
    {
        $this->openSession($this->seller);

        // Invalid PIN
        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.authorization.pin'), [
            'pin' => '9999',
        ]);
        $response->assertStatus(422)
            ->assertJson(['valid' => false]);

        // Valid PIN for manager
        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.authorization.pin'), [
            'pin' => '5678',
        ]);
        $response->assertOk()
            ->assertJson([
                'valid' => true,
                'supervisor_name' => $this->manager->name,
            ]);
    }

    public function test_seller_can_apply_excess_discount_with_valid_supervisor_pin(): void
    {
        $this->openSession($this->seller);
        $product = $this->product('1000.00');

        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.store'), [
            'items' => [
                ['product_id' => $product->id, 'discount' => '0'],
            ],
            'discount_total' => '200.00', // 20%
            'discount_reason' => 'Autorización especial presencial',
            'supervisor_pin' => '5678',
            'payments' => [
                ['method' => 'cash', 'amount' => '800.00', 'received' => '800.00'],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sales', [
            'total' => '800.00',
            'discount_total' => '200.00',
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertStringContainsString("Descuento autorizado por: {$this->manager->name}", $sale->notes);
    }

    public function test_remote_authorization_lifecycle_approval_and_use(): void
    {
        $this->openSession($this->seller);
        $product = $this->product('1000.00');

        // 1. Seller creates remote request
        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.authorization.store'), [
            'sale_data' => [
                'items' => [['product_id' => $product->id, 'discount' => '0']],
            ],
            'subtotal' => '1000.00',
            'discount_amount' => '150.00',
            'discount_percent' => '15.00',
            'reason' => 'Cliente VIP aniversario',
        ]);

        $response->assertCreated();
        $authId = $response->json('id');
        $this->assertDatabaseHas('discount_authorizations', [
            'id' => $authId,
            'status' => 'pending',
            'requested_by' => $this->seller->id,
        ]);

        // 2. Supervisor checks pending badge count and list
        $countResponse = $this->actingAs($this->manager)->getJson(route('admin.authorizations.count'));
        $countResponse->assertOk()->assertJson(['count' => 1]);

        $pendingResponse = $this->actingAs($this->manager)->getJson(route('admin.authorizations.pending'));
        $pendingResponse->assertOk()
            ->assertJsonCount(1, 'authorizations')
            ->assertJsonPath('authorizations.0.id', $authId);

        // 3. Supervisor approves request
        $auth = DiscountAuthorization::find($authId);
        $approveResponse = $this->actingAs($this->manager)->postJson(route('admin.authorizations.approve', $auth));
        $approveResponse->assertOk()->assertJson(['status' => 'approved']);

        // 4. Seller polls status
        $statusResponse = $this->actingAs($this->seller)->getJson(route('admin.pos.authorization.status', $auth));
        $statusResponse->assertOk()
            ->assertJson([
                'status' => 'approved',
                'authorized_by_name' => $this->manager->name,
            ]);

        // 5. Seller completes sale with authorization ID
        $saleResponse = $this->actingAs($this->seller)->postJson(route('admin.pos.store'), [
            'items' => [
                ['product_id' => $product->id, 'discount' => '0'],
            ],
            'discount_total' => '150.00',
            'discount_reason' => 'Cliente VIP aniversario',
            'discount_authorization_id' => $authId,
            'payments' => [
                ['method' => 'cash', 'amount' => '850.00', 'received' => '900.00'],
            ],
        ]);

        $saleResponse->assertOk();
        $this->assertEquals(DiscountAuthorizationStatus::Used, $auth->refresh()->status);
    }

    public function test_remote_authorization_rejection(): void
    {
        $this->openSession($this->seller);
        $product = $this->product('1000.00');

        $response = $this->actingAs($this->seller)->postJson(route('admin.pos.authorization.store'), [
            'sale_data' => [
                'items' => [['product_id' => $product->id, 'discount' => '0']],
            ],
            'subtotal' => '1000.00',
            'discount_amount' => '300.00',
            'discount_percent' => '30.00',
            'reason' => 'Petición extrema',
        ]);
        $auth = DiscountAuthorization::find($response->json('id'));

        // Supervisor rejects
        $rejectResponse = $this->actingAs($this->manager)->postJson(route('admin.authorizations.reject', $auth), [
            'reason' => 'Margen insuficiente para 30%',
        ]);
        $rejectResponse->assertOk()->assertJson(['status' => 'rejected']);

        // Seller polls status
        $statusResponse = $this->actingAs($this->seller)->getJson(route('admin.pos.authorization.status', $auth));
        $statusResponse->assertOk()
            ->assertJson([
                'status' => 'rejected',
                'rejection_reason' => 'Margen insuficiente para 30%',
            ]);
    }

    public function test_owner_sees_authorizations_from_all_stores_while_manager_only_sees_their_store(): void
    {
        $this->openSession($this->seller);

        // Store 2 setup
        $store2 = Store::create([
            'name' => 'Sucursal Norte',
            'code' => 'SUC-02',
            'currency' => 'USD',
            'timezone' => 'America/Mexico_City',
            'is_active' => true,
        ]);
        $seller2 = User::factory()->create([
            'store_id' => $store2->id,
            'is_active' => true,
        ]);
        $seller2->assignRole('Vendedor');
        $this->openSession($seller2);

        $owner = User::where('email', 'admin@joyeriaweb.com')->first();
        $owner->update(['supervisor_pin' => Hash::make('1234')]);

        // Request in Store 1
        $this->actingAs($this->seller)->postJson(route('admin.pos.authorization.store'), [
            'sale_data' => ['items' => []],
            'subtotal' => '1000.00',
            'discount_amount' => '150.00',
            'discount_percent' => '15.00',
            'reason' => 'Cliente frecuente Tienda 1',
        ]);

        // Request in Store 2
        $this->actingAs($seller2)->postJson(route('admin.pos.authorization.store'), [
            'sale_data' => ['items' => []],
            'subtotal' => '2000.00',
            'discount_amount' => '300.00',
            'discount_percent' => '15.00',
            'reason' => 'Promoción apertura Tienda 2',
        ]);

        // Manager in Store 1 only sees Store 1's request (count = 1)
        $managerCountResponse = $this->actingAs($this->manager)->getJson(route('admin.authorizations.count'));
        $managerCountResponse->assertOk()->assertJson(['count' => 1]);

        $managerPendingResponse = $this->actingAs($this->manager)->getJson(route('admin.authorizations.pending'));
        $managerPendingResponse->assertOk()
            ->assertJsonCount(1, 'authorizations')
            ->assertJsonFragment(['reason' => 'Cliente frecuente Tienda 1']);

        // Owner sees requests from BOTH stores (count = 2)
        $ownerCountResponse = $this->actingAs($owner)->getJson(route('admin.authorizations.count'));
        $ownerCountResponse->assertOk()->assertJson(['count' => 2]);

        $ownerPendingResponse = $this->actingAs($owner)->getJson(route('admin.authorizations.pending'));
        $ownerPendingResponse->assertOk()
            ->assertJsonCount(2, 'authorizations')
            ->assertJsonFragment(['reason' => 'Cliente frecuente Tienda 1'])
            ->assertJsonFragment(['reason' => 'Promoción apertura Tienda 2']);

        // Owner's PIN is valid even in Store 2 where owner is not directly assigned
        $pinResponse = $this->actingAs($seller2)->postJson(route('admin.pos.authorization.pin'), [
            'pin' => '1234',
        ]);
        $pinResponse->assertOk()->assertJson([
            'valid' => true,
            'supervisor_id' => $owner->id,
            'supervisor_name' => $owner->name,
        ]);
    }
}
