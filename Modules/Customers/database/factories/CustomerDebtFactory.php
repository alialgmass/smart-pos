<?php

namespace Modules\Customers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\Customer;
use Modules\Customers\Models\CustomerDebt;

/**
 * @extends Factory<CustomerDebt>
 */
class CustomerDebtFactory extends Factory
{
    protected $model = CustomerDebt::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'paid_amount' => 0,
            'status' => CustomerDebtStatus::Open,
        ];
    }
}
