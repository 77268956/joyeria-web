<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\StockCountStatus;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\User;
use App\Services\StockCountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockCountTest extends TestCase
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

    private function product(User $user, ProductStatus $status = ProductStatus::Available): Product
    {
        return Product::factory()->create([
            'store_id' => $user->store_id,
            'status' => $status,
        ]);
    }

    public function test_starting_a_count_snapshots_the_in_stock_pieces(): void
    {
        $owner = $this->owner();
        $available = $this->product($owner);
        $reserved = $this->product($owner, ProductStatus::Reserved);
        $sold = $this->product($owner, ProductStatus::Sold);

        $this->actingAs($owner)
            ->post(route('admin.stock-counts.store'))
            ->assertRedirect();

        $count = StockCount::firstOrFail();

        $this->assertSame(StockCountStatus::Open, $count->status);
        $this->assertSame($owner->id, $count->started_by);

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $available->id,
            'expected' => true,
        ]);

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $reserved->id,
        ]);

        $this->assertDatabaseMissing('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $sold->id,
        ]);
    }

    public function test_recording_updates_the_found_state_and_note(): void
    {
        $owner = $this->owner();
        $product = $this->product($owner);
        $count = app(StockCountService::class)->start($owner);

        $this->actingAs($owner)->post(route('admin.stock-counts.record', $count), [
            'items' => [
                ['product_id' => $product->id, 'found' => '0', 'note' => 'No estaba en vitrina'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $product->id,
            'found' => 0,
            'note' => 'No estaba en vitrina',
        ]);
    }

    public function test_finishing_moves_the_count_to_finished(): void
    {
        $owner = $this->owner();
        $count = app(StockCountService::class)->start($owner);

        $this->actingAs($owner)
            ->post(route('admin.stock-counts.finish', $count))
            ->assertRedirect();

        $count->refresh();

        $this->assertSame(StockCountStatus::Finished, $count->status);
        $this->assertSame($owner->id, $count->finished_by);
        $this->assertNotNull($count->finished_at);
    }

    public function test_applying_a_count_adjusts_missing_pieces_out_of_stock(): void
    {
        $owner = $this->owner();
        $missing = $this->product($owner);
        $count = app(StockCountService::class)->start($owner);

        app(StockCountService::class)->record($count, [
            ['product_id' => $missing->id, 'found' => '0'],
        ]);
        app(StockCountService::class)->finish($count, $owner);

        $this->actingAs($owner)
            ->post(route('admin.stock-counts.apply', $count))
            ->assertRedirect();

        $count->refresh();

        $this->assertSame(StockCountStatus::Applied, $count->status);
        $this->assertSame(ProductStatus::Withdrawn, $missing->refresh()->status);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $missing->id,
            'type' => 'adjustment',
            'quantity_delta' => -1,
            'reference_type' => StockCount::class,
            'reference_id' => $count->id,
        ]);
    }

    public function test_applying_a_count_adds_surplus_pieces_to_stock(): void
    {
        $owner = $this->owner();
        $count = app(StockCountService::class)->start($owner);

        $surplus = $this->product($owner);

        app(StockCountService::class)->record($count, [
            ['product_id' => $surplus->id, 'found' => '1'],
        ]);
        app(StockCountService::class)->finish($count, $owner);
        app(StockCountService::class)->apply($count, $owner);

        $this->assertDatabaseHas('stock_count_items', [
            'stock_count_id' => $count->id,
            'product_id' => $surplus->id,
            'expected' => false,
            'found' => 1,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $surplus->id,
            'type' => 'adjustment',
            'quantity_delta' => 1,
        ]);
    }

    public function test_a_count_cannot_be_applied_before_it_is_finished(): void
    {
        $owner = $this->owner();
        $count = app(StockCountService::class)->start($owner);

        $this->expectException(RuntimeException::class);

        app(StockCountService::class)->apply($count, $owner);
    }

    public function test_pieces_cannot_be_recorded_on_a_finished_count(): void
    {
        $owner = $this->owner();
        $product = $this->product($owner);
        $count = app(StockCountService::class)->start($owner);
        app(StockCountService::class)->finish($count, $owner);

        $this->expectException(RuntimeException::class);

        app(StockCountService::class)->record($count, [
            ['product_id' => $product->id, 'found' => '1'],
        ]);
    }

    public function test_a_seller_can_view_but_not_start_a_count(): void
    {
        $seller = User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();

        $this->actingAs($seller)->get(route('admin.stock-counts.index'))->assertOk();
        $this->actingAs($seller)->post(route('admin.stock-counts.store'))->assertForbidden();
    }
}
