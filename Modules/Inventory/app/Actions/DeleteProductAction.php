<?php

namespace Modules\Inventory\Actions;

use Modules\Inventory\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
