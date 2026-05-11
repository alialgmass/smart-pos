<?php

namespace Modules\Restaurant\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Table;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'table_id' => Table::factory(),
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.fake()->unique()->numerify('########'),
            'status' => OrderStatus::Open,
            'notes' => null,
        ];
    }
}
