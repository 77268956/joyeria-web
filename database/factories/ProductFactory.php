<?php

namespace Database\Factories;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Metal;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-###-#####')),
            'barcode' => (string) fake()->unique()->numerify('############'),
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->sentence(),
            'category_id' => fn () => Category::firstOrCreate(
                ['slug' => 'anillos'],
                ['name' => 'Anillos', 'is_active' => true]
            )->id,
            'metal_id' => fn () => Metal::firstOrCreate(
                ['code' => 'GOLD'],
                ['name' => 'Oro', 'is_precious' => true]
            )->id,
            'purity_id' => null,
            'weight_g' => fake()->randomFloat(3, 1, 50),
            'cost' => fake()->randomFloat(2, 10, 500),
            'price' => fake()->randomFloat(2, 50, 1500),
            'location_id' => null,
            'status' => ProductStatus::Available,
            'origin' => ProductOrigin::New,
            'is_featured' => false,
            'is_public' => true,
        ];
    }

    /**
     * Indicate a piece that was bought second hand.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'origin' => ProductOrigin::UsedPurchase,
        ]);
    }
}
