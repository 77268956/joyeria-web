<?php

namespace Database\Factories;

use App\Enums\MetalPurchaseStatus;
use App\Enums\PayoutMethod;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\MetalPurchase;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetalPurchase>
 */
class MetalPurchaseFactory extends Factory
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
            'number' => 'COM-'.fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'cash_session_id' => CashSession::factory(),
            'status' => MetalPurchaseStatus::Paid,
            'total_amount' => '100.00',
            'payout_method' => PayoutMethod::Cash,
            'approved_by' => null,
            'approved_at' => null,
            'approval_note' => null,
            'notes' => null,
        ];
    }
}
