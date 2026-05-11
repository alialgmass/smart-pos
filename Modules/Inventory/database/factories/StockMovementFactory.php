<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\StockMovement;
use Modules\Shared\Enums\StockMovementType;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $qtyChange = fake()->randomFloat(2, 1, 50);
        $qtyBefore = fake()->randomFloat(2, 0, 100);
        $qtyAfter = $qtyBefore + $qtyChange;

        return [
            'type' => StockMovementType::Purchase,
            'qty_change' => $qtyChange,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'reference_type' => null,
            'reference_id' => null,
            'user_id' => null,
        ];
    }
}
