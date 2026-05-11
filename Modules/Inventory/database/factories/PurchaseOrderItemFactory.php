<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\PurchaseOrderItem;

/**
 * @extends Factory<PurchaseOrderItem>
 */
class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        return [
            'qty' => fake()->randomFloat(2, 1, 100),
            'unit_cost' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
