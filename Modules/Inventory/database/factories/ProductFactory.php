<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Enums\ProductStatus;
use Modules\Inventory\Models\Product;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'barcode' => fake()->unique()->numerify('##########'),
            'price' => fake()->randomFloat(2, 1, 999),
            'cost' => fake()->randomFloat(2, 1, 500),
            'stock_qty' => fake()->randomFloat(2, 0, 100),
            'min_stock' => fake()->randomFloat(2, 0, 10),
            'status' => ProductStatus::Active,
            'has_variants' => false,
        ];
    }

    public function withVariants(): static
    {
        return $this->state(['has_variants' => true]);
    }
}
