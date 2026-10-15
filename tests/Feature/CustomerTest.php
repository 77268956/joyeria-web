<?php

namespace Tests\Feature;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    /**
     * @return array<string, string>
     */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Ana Torres',
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'phone' => '555-0101',
            'email' => 'ana@example.com',
        ], $overrides);
    }

    public function test_a_user_without_permission_cannot_list_customers(): void
    {
        $user = User::factory()->create(['store_id' => Store::first()->id, 'is_active' => true]);

        $this->actingAs($user)->get(route('admin.customers.index'))->assertForbidden();
    }

    public function test_a_seller_can_create_a_customer(): void
    {
        $response = $this->actingAs($this->seller())->post(route('admin.customers.store'), $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'full_name' => 'Ana Torres',
            'document_type' => 'DNI',
            'document_number' => '12345678',
        ]);
    }

    public function test_the_document_number_is_unique_per_store(): void
    {
        $this->actingAs($this->seller())->post(route('admin.customers.store'), $this->validPayload());

        $response = $this->actingAs($this->seller())
            ->from(route('admin.customers.create'))
            ->post(route('admin.customers.store'), $this->validPayload(['full_name' => 'Otra Persona']));

        $response->assertSessionHasErrors('document_number');
        $this->assertSame(1, Customer::count());
    }

    public function test_the_id_photo_is_stored_on_the_private_disk(): void
    {
        Storage::fake('local');

        $this->actingAs($this->seller())->post(route('admin.customers.store'), $this->validPayload([
            'id_photo' => UploadedFile::fake()->create('id.jpg', 100, 'image/jpeg'),
        ]));

        $customer = Customer::first();

        $this->assertNotNull($customer->id_photo_path);
        Storage::disk('local')->assertExists($customer->id_photo_path);
    }

    public function test_the_photo_route_streams_the_private_file(): void
    {
        Storage::fake('local');

        $customer = Customer::factory()->create([
            'store_id' => Store::first()->id,
            'id_photo_path' => 'customer-ids/id.jpg',
        ]);
        Storage::disk('local')->put('customer-ids/id.jpg', 'fake-image');

        $this->actingAs($this->seller())
            ->get(route('admin.customers.photo', $customer))
            ->assertOk();
    }

    public function test_search_returns_matching_customers(): void
    {
        Customer::factory()->create([
            'store_id' => Store::first()->id,
            'full_name' => 'Beatriz Salazar',
            'document_number' => '99999999',
        ]);

        $response = $this->actingAs($this->seller())->getJson(route('admin.customers.search', ['q' => 'Beatriz']));

        $response->assertOk();
        $response->assertJsonPath('customers.0.full_name', 'Beatriz Salazar');
    }

    public function test_the_detail_shows_the_sales_history(): void
    {
        $seller = $this->seller();
        $customer = Customer::factory()->create(['store_id' => $seller->store_id]);
        $session = CashSession::factory()->create(['store_id' => $seller->store_id, 'user_id' => $seller->id]);
        $sale = Sale::factory()->create([
            'store_id' => $seller->store_id,
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'cash_session_id' => $session->id,
        ]);

        $this->actingAs($this->seller())
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee($sale->number);
    }

    public function test_a_seller_can_update_a_customer(): void
    {
        $customer = Customer::factory()->create(['store_id' => Store::first()->id]);

        $response = $this->actingAs($this->seller())->put(route('admin.customers.update', $customer), $this->validPayload([
            'full_name' => 'Nombre Actualizado',
        ]));

        $response->assertRedirect(route('admin.customers.show', $customer));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Nombre Actualizado']);
    }
}
