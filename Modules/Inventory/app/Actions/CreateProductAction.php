<?php

namespace Modules\Inventory\Actions;

use Modules\Inventory\Models\Product;

class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Product
    {
        return Product::create($data);
    }
}
