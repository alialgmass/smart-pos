<?php

namespace Modules\Sales\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 1, 100);
        $qty = fake()->randomFloat(2, 1, 10);

        return [
            'tenant_id' => 1,
            'sale_id' => Sale::factory(),
            'product_id' => null,
            'variant_id' => null,
            'name' => fake()->word(),
            'price' => $price,
            'cost' => fake()->randomFloat(2, 0.5, $price),
            'qty' => $qty,
            'discount' => 0,
            'tax_amount' => round($price * $qty * 0.14, 2),
            'total' => round($price * $qty, 2),
        ];
    }
}
