<?php

namespace Database\Factories;

use App\Enums\CashSessionStatus;
use App\Models\CashSession;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashSession>
 */
class CashSessionFactory extends Factory
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
            'user_id' => User::factory(),
            'status' => CashSessionStatus::Open,
            'opened_at' => now(),
            'opening_amount' => '0.00',
            'closed_at' => null,
            'closed_by' => null,
        ];
    }

    /**
     * Indicate that the shift is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashSessionStatus::Open,
            'closed_at' => null,
        ]);
    }

    /**
     * Indicate that the shift is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashSessionStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
