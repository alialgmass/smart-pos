<?php

namespace Modules\Restaurant\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'variant_id' => null,
            'name' => fake()->word(),
            'price' => fake()->randomFloat(2, 1, 100),
            'qty' => fake()->randomFloat(2, 1, 10),
            'notes' => null,
            'sent_to_kitchen_at' => null,
        ];
    }
}
