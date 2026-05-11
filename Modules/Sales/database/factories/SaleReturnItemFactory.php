<?php

namespace Modules\Sales\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\SaleReturn;
use Modules\Sales\Models\SaleReturnItem;

/**
 * @extends Factory<SaleReturnItem>
 */
class SaleReturnItemFactory extends Factory
{
    protected $model = SaleReturnItem::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'sale_return_id' => SaleReturn::factory(),
            'sale_item_id' => SaleItem::factory(),
            'product_id' => null,
            'variant_id' => null,
            'qty' => fake()->randomFloat(2, 1, 5),
            'refund_amount' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
