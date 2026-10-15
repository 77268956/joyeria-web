<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\CashSession;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
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
            'number' => 'VTA-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => null,
            'user_id' => User::factory(),
            'cash_session_id' => CashSession::factory(),
            'subtotal' => '100.00',
            'discount_total' => '0.00',
            'total' => '100.00',
            'status' => SaleStatus::Completed,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'notes' => null,
        ];
    }
}
