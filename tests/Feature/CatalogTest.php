<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Metal;
use App\Models\MetalPurity;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_metals_and_purities_are_seeded(): void
    {
        $gold = Metal::where('code', 'GOLD')->first();
        $this->assertNotNull($gold);
        $this->assertTrue($gold->is_precious);

        $silver = Metal::where('code', 'SILVER')->first();
        $this->assertNotNull($silver);
        $this->assertTrue($silver->is_precious);

        // Check gold purities
        $purity18k = MetalPurity::where('code', '18K')->first();
        $this->assertNotNull($purity18k);
        $this->assertEquals('0.7500', $purity18k->fineness);
        $this->assertEquals($gold->id, $purity18k->metal_id);

        // Check silver purity 925
        $purity925 = MetalPurity::where('code', '925')->first();
        $this->assertNotNull($purity925);
        $this->assertEquals('0.9250', $purity925->fineness);
        $this->assertEquals($silver->id, $purity925->metal_id);
    }

    public function test_categories_and_locations_are_seeded(): void
    {
        $anillos = Category::where('slug', 'anillos')->first();
        $this->assertNotNull($anillos);
        $this->assertEquals('Anillos', $anillos->name);

        $store = Store::first();
        $vitrina = Location::where('store_id', $store->id)->where('type', 'vitrina')->first();
        $this->assertNotNull($vitrina);
    }
}
