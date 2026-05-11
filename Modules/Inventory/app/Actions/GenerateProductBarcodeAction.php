<?php

namespace Modules\Inventory\Actions;

use Modules\Inventory\Models\Product;

class GenerateProductBarcodeAction
{
    public function execute(Product $product): string
    {
        do {
            $barcode = (string) random_int(1000000000, 9999999999);
        } while (Product::query()->withoutGlobalScope('tenant')->where('barcode', $barcode)->exists());

        $product->update(['barcode' => $barcode]);

        return $barcode;
    }
}
