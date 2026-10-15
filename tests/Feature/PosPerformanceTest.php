<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PosPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
    }

    protected function availableProductWithName(string $name): Product
    {
        return Product::factory()->create([
            'store_id' => Store::first()->id,
            'name' => $name,
            'status' => ProductStatus::Available,
        ]);
    }

    /**
     * Count the database queries performed while running a callback.
     */
    protected function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }

    public function test_pos_search_query_count_does_not_grow_with_result_size(): void
    {
        $seller = $this->seller();
        app(CashSessionService::class)->open($seller, '0.00');
        $this->actingAs($seller);

        $this->availableProductWithName('Anillo de oro clásico');

        // Warm up one-time queries (session, permissions) so only per-search work is measured.
        $this->getJson(route('admin.pos.search', ['q' => 'Anillo']))->assertOk();

        $singleQueryCount = $this->countQueries(function (): void {
            $this->getJson(route('admin.pos.search', ['q' => 'Anillo']))->assertOk();
        });

        Product::factory()->count(12)->create([
            'store_id' => Store::first()->id,
            'name' => 'Anillo de oro adicional',
            'status' => ProductStatus::Available,
        ]);

        $manyQueryCount = $this->countQueries(function (): void {
            $this->getJson(route('admin.pos.search', ['q' => 'Anillo']))->assertOk();
        });

        $this->assertSame(
            $singleQueryCount,
            $manyQueryCount,
            'POS search must eager-load relations so queries stay constant (no N+1).',
        );

        $this->assertLessThanOrEqual(15, $manyQueryCount, 'POS search should stay within a small query budget.');
    }

    public function test_pos_search_returns_at_most_twenty_results(): void
    {
        $seller = $this->seller();
        app(CashSessionService::class)->open($seller, '0.00');

        Product::factory()->count(25)->create([
            'store_id' => Store::first()->id,
            'name' => 'Pulsera dorada',
            'status' => ProductStatus::Available,
        ]);

        $response = $this->actingAs($seller)->getJson(route('admin.pos.search', ['q' => 'Pulsera']));

        $response->assertOk();
        $this->assertCount(20, $response->json('products'));
    }
}
