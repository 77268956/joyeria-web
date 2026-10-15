<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Metal;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryBalanceVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->store = Store::first();
        $this->user = User::where('email', 'admin@joyeriaweb.com')->first();
    }

    public function test_command_succeeds_when_everything_is_consistent(): void
    {
        $this->artisan('inventory:verify-balances')->assertExitCode(0);
    }

    public function test_command_detects_a_piece_status_drift(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);
        app(InventoryService::class)->registerProductEntry($product, null, 'Alta', $this->user->id);

        DB::table('products')->where('id', $product->id)->update(['status' => ProductStatus::Withdrawn->value]);

        $this->artisan('inventory:verify-balances')
            ->expectsOutputToContain($product->sku)
            ->assertExitCode(1);
    }

    public function test_command_detects_a_lot_weight_drift(): void
    {
        $metal = Metal::where('code', 'GOLD')->first();

        $lot = app(InventoryService::class)->createMaterialLot([
            'store_id' => $this->store->id,
            'code' => 'LOT-DRIFT-001',
            'metal_id' => $metal->id,
            'purity_id' => $metal->purities()->where('code', '18K')->first()->id,
            'initial_weight_g' => '50.000',
            'location_id' => null,
            'notes' => null,
        ], $this->user->id);

        DB::table('material_lots')->where('id', $lot->id)->update(['current_weight_g' => '10.000']);

        $this->artisan('inventory:verify-balances')
            ->expectsOutputToContain($lot->code)
            ->assertExitCode(1);
    }

    public function test_command_can_be_scoped_to_a_single_store(): void
    {
        $this->artisan('inventory:verify-balances', ['--store' => $this->store->id])
            ->assertExitCode(0);
    }
}
