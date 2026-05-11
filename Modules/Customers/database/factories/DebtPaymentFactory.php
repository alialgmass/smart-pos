<?php

namespace Modules\Customers\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customers\Models\CustomerDebt;
use Modules\Customers\Models\DebtPayment;
use Modules\Shared\Enums\PaymentMethod;

/**
 * @extends Factory<DebtPayment>
 */
class DebtPaymentFactory extends Factory
{
    protected $model = DebtPayment::class;

    public function definition(): array
    {
        return [
            'debt_id' => CustomerDebt::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_method' => PaymentMethod::Cash,
            'user_id' => User::factory(),
        ];
    }
}
