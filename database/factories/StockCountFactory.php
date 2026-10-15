<?php

namespace Database\Factories;

use App\Enums\StockCountStatus;
use App\Models\StockCount;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockCount>
 */
class StockCountFactory extends Factory
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
            'status' => StockCountStatus::Open,
            'started_by' => User::factory(),
            'started_at' => now(),
            'finished_at' => null,
            'finished_by' => null,
            'applied_at' => null,
            'applied_by' => null,
            'notes' => null,
        ];
    }
}
