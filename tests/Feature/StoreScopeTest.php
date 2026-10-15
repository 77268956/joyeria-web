<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_model_with_store_scope_automatically_sets_store_id_on_create(): void
    {
        $store = Store::first();
        $user = User::factory()->create(['store_id' => $store->id]);

        $this->actingAs($user);

        $customer = Customer::create([
            'full_name' => 'Cliente Prueba',
            'document_type' => 'DNI',
            'document_number' => 'DOC123456',
        ]);

        $this->assertEquals($store->id, $customer->store_id);
    }

    public function test_user_can_only_see_records_from_their_own_store(): void
    {
        $storeA = Store::first();
        $storeB = Store::create([
            'name' => 'Segunda Sucursal',
            'code' => 'SUC02',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $customerA = Customer::create([
            'store_id' => $storeA->id,
            'full_name' => 'Cliente Sucursal A',
            'document_type' => 'DNI',
            'document_number' => 'DOC-A-01',
        ]);

        $customerB = Customer::create([
            'store_id' => $storeB->id,
            'full_name' => 'Cliente Sucursal B',
            'document_type' => 'DNI',
            'document_number' => 'DOC-B-01',
        ]);

        $userA = User::factory()->create(['store_id' => $storeA->id]);

        $this->actingAs($userA);

        $visibleCustomers = Customer::all();

        $this->assertTrue($visibleCustomers->contains($customerA));
        $this->assertFalse($visibleCustomers->contains($customerB));
    }

    public function test_all_stores_scope_bypasses_the_global_filter(): void
    {
        $storeA = Store::first();
        $storeB = Store::create([
            'name' => 'Segunda Sucursal',
            'code' => 'SUC02',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        Customer::create([
            'store_id' => $storeA->id,
            'full_name' => 'Cliente A',
            'document_type' => 'DNI',
            'document_number' => 'DOC-A-02',
        ]);

        Customer::create([
            'store_id' => $storeB->id,
            'full_name' => 'Cliente B',
            'document_type' => 'DNI',
            'document_number' => 'DOC-B-02',
        ]);

        $userA = User::factory()->create(['store_id' => $storeA->id]);
        $this->actingAs($userA);

        $allCustomers = Customer::allStores()->get();

        $this->assertCount(2, $allCustomers);
    }
}
