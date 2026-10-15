<?php

namespace Tests\Feature;

use App\Enums\LayawayStatus;
use App\Enums\ProductStatus;
use App\Models\Customer;
use App\Models\Layaway;
use App\Models\LayawayPayment;
use App\Models\Product;
use App\Models\User;
use App\Services\CashSessionService;
use App\Services\LayawayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class LayawayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function owner(): User
    {
        return User::where('email', 'admin@joyeriaweb.com')->firstOrFail();
    }

    private function product(User $user, string $price = '120.00'): Product
    {
        return Product::factory()->create([
            'store_id' => $user->store_id,
            'price' => $price,
            'status' => ProductStatus::Available,
        ]);
    }

    private function customer(User $user): Customer
    {
        return Customer::factory()->create(['store_id' => $user->store_id]);
    }

    public function test_creating_a_layaway_reserves_the_piece_and_records_the_initial_payment(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '100.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $response = $this->actingAs($owner)->post(route('admin.layaways.store'), [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
            'reference' => 'APT-TEST',
        ]);

        $layaway = Layaway::firstOrFail();

        $response->assertRedirect(route('admin.layaways.show', $layaway));

        $this->assertDatabaseHas('layaways', [
            'id' => $layaway->id,
            'number' => $layaway->number,
            'total_price' => '120.00',
            'paid_total' => '20.00',
            'balance' => '100.00',
            'status' => LayawayStatus::Active->value,
        ]);

        $this->assertSame(ProductStatus::Reserved, $product->refresh()->status);

        $this->assertDatabaseHas('layaway_payments', [
            'layaway_id' => $layaway->id,
            'cash_session_id' => $session->id,
            'method' => 'cash',
            'amount' => '20.00',
        ]);

        $this->assertSame('120.00', app(CashSessionService::class)->expectedTotals($session)['cash']);
    }

    public function test_partial_payments_reduce_the_balance_and_count_toward_the_cash(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $layaway = app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
        ]);

        $this->actingAs($owner)->post(route('admin.layaways.payment', $layaway), [
            'method' => 'card',
            'amount' => '50.00',
        ])->assertRedirect();

        $layaway->refresh();

        $this->assertSame('50.00', (string) $layaway->balance);
        $this->assertSame('70.00', (string) $layaway->paid_total);

        $totals = app(CashSessionService::class)->expectedTotals($session);

        $this->assertSame('20.00', $totals['cash']);
        $this->assertSame('50.00', $totals['card']);
    }

    public function test_a_payment_cannot_exceed_the_outstanding_balance(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $layaway = app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(LayawayService::class)->addPayment($layaway, $session, $owner, [
            'method' => 'cash',
            'amount' => '200.00',
        ]);
    }

    public function test_a_layaway_cannot_be_delivered_with_an_outstanding_balance(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $layaway = app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
        ]);

        $this->expectException(RuntimeException::class);

        app(LayawayService::class)->complete($layaway, $session, $owner);
    }

    public function test_completing_a_paid_layaway_creates_a_sale_and_marks_the_piece_sold(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $layaway = app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '120.00',
            'initial_method' => 'cash',
        ]);

        $this->assertTrue($layaway->isFullyPaid());

        $this->actingAs($owner)
            ->post(route('admin.layaways.complete', $layaway))
            ->assertRedirect(route('admin.layaways.show', $layaway));

        $layaway->refresh();

        $this->assertSame(LayawayStatus::Completed, $layaway->status);
        $this->assertNotNull($layaway->completed_sale_id);
        $this->assertSame(ProductStatus::Sold, $product->refresh()->status);

        $this->assertDatabaseHas('sales', [
            'id' => $layaway->completed_sale_id,
            'customer_id' => $customer->id,
            'total' => '120.00',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $layaway->completed_sale_id,
            'product_id' => $product->id,
            'line_total' => '120.00',
        ]);
    }

    public function test_cancelling_a_layaway_releases_the_piece_back_to_stock(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $layaway = app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
        ]);

        $this->actingAs($owner)->post(route('admin.layaways.cancel', $layaway), [
            'reason' => 'El cliente desistió',
        ])->assertRedirect(route('admin.layaways.show', $layaway));

        $layaway->refresh();

        $this->assertSame(LayawayStatus::Cancelled, $layaway->status);
        $this->assertSame('El cliente desistió', $layaway->cancel_reason);
        $this->assertSame(ProductStatus::Available, $product->refresh()->status);
    }

    public function test_a_seller_may_manage_layaways(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
        app(CashSessionService::class)->open($seller, '0.00');
        $product = $this->product($seller);
        $customer = $this->customer($seller);

        $this->actingAs($seller)->post(route('admin.layaways.store'), [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'initial_amount' => '10.00',
            'initial_method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseCount('layaways', 1);
    }

    public function test_a_user_without_permission_cannot_access_layaways(): void
    {
        $user = User::factory()->create(['store_id' => $this->owner()->store_id]);

        $this->actingAs($user)->get(route('admin.layaways.index'))->assertForbidden();
    }

    public function test_the_due_date_cannot_exceed_the_configured_maximum(): void
    {
        $owner = $this->owner();
        app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $this->actingAs($owner)->post(route('admin.layaways.store'), [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(45)->toDateString(),
            'initial_amount' => '10.00',
            'initial_method' => 'cash',
        ])->assertSessionHasErrors('due_date');

        $this->assertDatabaseCount('layaways', 0);
    }

    public function test_the_payment_model_persists_the_collected_instalment(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);

        $layaway = Layaway::factory()->create([
            'store_id' => $owner->store_id,
            'product_id' => $product->id,
            'customer_id' => $this->customer($owner)->id,
            'user_id' => $owner->id,
        ]);

        LayawayPayment::factory()->create([
            'layaway_id' => $layaway->id,
            'cash_session_id' => $session->id,
            'user_id' => $owner->id,
            'amount' => '30.00',
        ]);

        $this->assertDatabaseHas('layaway_payments', [
            'layaway_id' => $layaway->id,
            'amount' => '30.00',
        ]);
    }

    public function test_creating_a_layaway_for_an_unavailable_piece_fails(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = Product::factory()->create([
            'store_id' => $owner->store_id,
            'price' => '120.00',
            'status' => ProductStatus::Sold,
        ]);
        $customer = $this->customer($owner);

        $this->expectException(RuntimeException::class);

        app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '20.00',
            'initial_method' => 'cash',
        ]);
    }

    public function test_the_initial_payment_cannot_be_negative(): void
    {
        $owner = $this->owner();
        $session = app(CashSessionService::class)->open($owner, '0.00');
        $product = $this->product($owner);
        $customer = $this->customer($owner);

        $this->expectException(InvalidArgumentException::class);

        app(LayawayService::class)->create($owner, $session, [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(10)->toDateString(),
            'initial_amount' => '-5.00',
            'initial_method' => 'cash',
        ]);
    }
}
