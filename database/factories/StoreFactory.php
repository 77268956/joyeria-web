<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Joyería '.fake()->city(),
            'code' => strtoupper(fake()->unique()->bothify('JOY##')),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ];
    }
}
