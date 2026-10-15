<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Metal;
use App\Models\MetalPurity;
use App\Models\Store;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Seed initial catalogs for metals, purities, categories, and locations.
     */
    public function run(): void
    {
        // 1. Metals
        $gold = Metal::firstOrCreate(
            ['code' => 'GOLD'],
            ['name' => 'Oro', 'is_precious' => true]
        );

        $silver = Metal::firstOrCreate(
            ['code' => 'SILVER'],
            ['name' => 'Plata', 'is_precious' => true]
        );

        $steel = Metal::firstOrCreate(
            ['code' => 'STEEL'],
            ['name' => 'Acero', 'is_precious' => false]
        );

        $fantasy = Metal::firstOrCreate(
            ['code' => 'FANTASY'],
            ['name' => 'Fantasía', 'is_precious' => false]
        );

        // 2. Metal Purities
        $purities = [
            // Gold purities
            ['metal_id' => $gold->id, 'code' => '24K', 'label' => 'Oro 24k (Puro)', 'fineness' => 1.0000],
            ['metal_id' => $gold->id, 'code' => '18K', 'label' => 'Oro 18k', 'fineness' => 0.7500],
            ['metal_id' => $gold->id, 'code' => '14K', 'label' => 'Oro 14k', 'fineness' => 0.5833],
            ['metal_id' => $gold->id, 'code' => '10K', 'label' => 'Oro 10k', 'fineness' => 0.4167],
            // Silver purities
            ['metal_id' => $silver->id, 'code' => '950', 'label' => 'Plata Ley 950', 'fineness' => 0.9500],
            ['metal_id' => $silver->id, 'code' => '925', 'label' => 'Plata Ley 925 (Sterling)', 'fineness' => 0.9250],
        ];

        foreach ($purities as $purity) {
            MetalPurity::firstOrCreate(
                ['metal_id' => $purity['metal_id'], 'code' => $purity['code']],
                [
                    'label' => $purity['label'],
                    'fineness' => $purity['fineness'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Categories
        $categories = [
            ['name' => 'Anillos', 'slug' => 'anillos'],
            ['name' => 'Cadenas y Collares', 'slug' => 'cadenas-y-collares'],
            ['name' => 'Aretes y Pendientes', 'slug' => 'aretes-y-pendientes'],
            ['name' => 'Pulseras y Brazaletes', 'slug' => 'pulseras-y-brazaletes'],
            ['name' => 'Dijes y Medallas', 'slug' => 'dijes-y-medallas'],
            ['name' => 'Relojes', 'slug' => 'relojes'],
            ['name' => 'Ocasión', 'slug' => 'ocasion'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }

        // 4. Default Store Locations
        $store = Store::first();
        if ($store) {
            $locations = [
                ['name' => 'Vitrina Principal', 'type' => 'vitrina'],
                ['name' => 'Caja Fuerte', 'type' => 'caja_fuerte'],
                ['name' => 'Taller', 'type' => 'taller'],
                ['name' => 'Bodega', 'type' => 'bodega'],
            ];

            foreach ($locations as $loc) {
                Location::firstOrCreate(
                    ['store_id' => $store->id, 'name' => $loc['name']],
                    ['type' => $loc['type'], 'is_active' => true]
                );
            }
        }
    }
}
