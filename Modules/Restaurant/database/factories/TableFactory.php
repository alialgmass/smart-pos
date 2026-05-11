<?php

namespace Modules\Restaurant\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Table;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'name' => 'Table '.fake()->numberBetween(1, 50),
            'capacity' => fake()->numberBetween(2, 8),
            'status' => TableStatus::Available,
            'position_x' => null,
            'position_y' => null,
        ];
    }
}
