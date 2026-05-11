<?php

namespace Modules\Sales\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sales\Enums\RefundMethod;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleReturn;

/**
 * @extends Factory<SaleReturn>
 */
class SaleReturnFactory extends Factory
{
    protected $model = SaleReturn::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'sale_id' => Sale::factory(),
            'customer_id' => null,
            'user_id' => User::factory(),
            'refund_method' => RefundMethod::Cash,
            'total_refund' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
