<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\CashSession;
use App\Models\Layaway;
use App\Models\LayawayPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LayawayPayment>
 */
class LayawayPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'layaway_id' => Layaway::factory(),
            'cash_session_id' => CashSession::factory(),
            'method' => PaymentMethod::Cash,
            'amount' => '10.00',
            'reference' => null,
            'user_id' => User::factory(),
            'created_at' => now(),
        ];
    }
}
