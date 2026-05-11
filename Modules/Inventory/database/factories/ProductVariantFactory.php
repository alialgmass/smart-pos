<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\ProductVariant;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'sku' => fake()->unique()->ean8(),
            'barcode' => fake()->unique()->numerify('##########'),
            'price' => fake()->randomFloat(2, 1, 999),
            'cost' => fake()->randomFloat(2, 1, 500),
            'stock_qty' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
