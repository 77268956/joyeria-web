<?php

namespace Database\Seeders;

use App\Enums\MetalPriceSource;
use App\Enums\ProductOrigin;
use App\Models\Category;
use App\Models\Location;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurity;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

/**
 * Demo catalog for local environments only. Never runs in production.
 */
class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first();
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        if (! $store || ! $owner) {
            return;
        }

        $inventory = app(InventoryService::class);
        $gold = Metal::where('code', 'GOLD')->first();
        $silver = Metal::where('code', 'SILVER')->first();

        $this->seedMetalPrices($store, $owner, $gold, $silver);

        $gold18k = MetalPurity::where('code', '18K')->first();
        $silver925 = MetalPurity::where('code', '925')->first();
        $vitrina = Location::where('store_id', $store->id)->where('type', 'vitrina')->first();

        $categories = Category::pluck('id', 'slug');
        $samples = [
            ['Anillo solitario', 'anillos', $gold, $gold18k, '4.250', '180.00', '420.00'],
            ['Collar cadena tejida', 'cadenas-y-collares', $gold, $gold18k, '12.800', '540.00', '1180.00'],
            ['Aretes de gota', 'aretes-y-pendientes', $silver, $silver925, '6.100', '48.00', '130.00'],
            ['Pulsera rígida', 'pulseras-y-brazaletes', $gold, $gold18k, '18.400', '760.00', '1650.00'],
            ['Dije corazón', 'dijes-y-medallas', $silver, $silver925, '3.200', '22.00', '65.00'],
        ];

        foreach ($samples as [$name, $slug, $metal, $purity, $weight, $cost, $price]) {
            if (Product::withoutGlobalScopes()->where('store_id', $store->id)->where('name', $name)->exists()) {
                continue;
            }

            $product = Product::create([
                'store_id' => $store->id,
                'sku' => Product::generateSku($store->id),
                'barcode' => Product::generateBarcode($store->id),
                'name' => $name,
                'category_id' => $categories[$slug] ?? $categories->first(),
                'metal_id' => $metal?->id,
                'purity_id' => $purity?->id,
                'weight_g' => $weight,
                'cost' => $cost,
                'price' => $price,
                'location_id' => $vitrina?->id,
                'origin' => ProductOrigin::New,
                'is_featured' => true,
                'is_public' => true,
            ]);

            $inventory->registerProductEntry($product, null, 'Carga de demostración', $owner->id);
        }
    }

    /**
     * Seed a starting price per gram for each precious metal (demo only).
     */
    protected function seedMetalPrices(Store $store, User $owner, ?Metal $gold, ?Metal $silver): void
    {
        $samples = [
            [$gold, '74.5000'],
            [$silver, '0.9500'],
        ];

        foreach ($samples as [$metal, $pricePerGram]) {
            if (! $metal) {
                continue;
            }

            if (MetalPrice::withoutGlobalScopes()->where('store_id', $store->id)->where('metal_id', $metal->id)->exists()) {
                continue;
            }

            MetalPrice::create([
                'store_id' => $store->id,
                'metal_id' => $metal->id,
                'price_per_gram' => $pricePerGram,
                'source' => MetalPriceSource::Manual,
                'price_date' => now()->toDateString(),
                'set_by' => $owner->id,
                'reason' => 'Precio inicial de demostración',
            ]);
        }
    }
}
