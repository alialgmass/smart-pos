<?php

namespace Modules\Inventory\Actions;

use Modules\Inventory\Models\Product;

class UpdateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }
}
