<?php

namespace Database\Factories;

use App\Enums\LayawayStatus;
use App\Models\Customer;
use App\Models\Layaway;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Layaway>
 */
class LayawayFactory extends Factory
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
            'number' => 'APT-'.fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'total_price' => '120.00',
            'paid_total' => '0.00',
            'balance' => '120.00',
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => LayawayStatus::Active,
            'cancel_reason' => null,
            'completed_sale_id' => null,
        ];
    }
}
