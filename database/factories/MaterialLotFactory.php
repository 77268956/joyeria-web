<?php

namespace Database\Factories;

use App\Enums\MaterialLotStatus;
use App\Models\MaterialLot;
use App\Models\Metal;
use App\Models\MetalPurity;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialLot>
 */
class MaterialLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weight = fake()->randomFloat(3, 5, 200);

        return [
            'store_id' => Store::factory(),
            'code' => strtoupper(fake()->unique()->bothify('LOT-###-#####')),
            'metal_id' => fn () => Metal::firstOrCreate(
                ['code' => 'GOLD'],
                ['name' => 'Oro', 'is_precious' => true]
            )->id,
            'purity_id' => function (): int {
                $gold = Metal::firstOrCreate(['code' => 'GOLD'], ['name' => 'Oro', 'is_precious' => true]);

                return MetalPurity::firstOrCreate(
                    ['metal_id' => $gold->id, 'code' => '18K'],
                    ['label' => 'Oro 18k', 'fineness' => 0.7500, 'is_active' => true]
                )->id;
            },
            'initial_weight_g' => $weight,
            'current_weight_g' => $weight,
            'location_id' => null,
            'status' => MaterialLotStatus::Open,
            'notes' => null,
        ];
    }
}
