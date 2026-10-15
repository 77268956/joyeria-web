<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'full_name' => fake()->name(),
            'document_type' => 'DNI',
            'document_number' => (string) fake()->unique()->numerify('########'),
            'phone' => fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'birth_date' => null,
            'id_photo_path' => null,
            'notes' => null,
        ];
    }
}
