<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
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
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+1555#######'),
            'message' => fake()->paragraph(),
            'status' => ContactMessage::STATUS_NEW,
            'ip' => fake()->ipv4(),
        ];
    }
}
