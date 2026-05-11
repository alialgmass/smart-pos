<?php

namespace Modules\Customers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customers\Models\Customer;
use Modules\Customers\Models\LoyaltyTransaction;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'points' => fake()->numberBetween(1, 100),
            'type' => fake()->randomElement([1, 2]),
        ];
    }
}
