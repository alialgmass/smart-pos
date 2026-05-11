<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' Plan',
            'price_monthly' => fake()->randomFloat(2, 10, 100),
            'max_users' => fake()->numberBetween(1, 10),
            'max_products' => fake()->numberBetween(50, 1000),
            'features' => [fake()->word()],
        ];
    }
}
