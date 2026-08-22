<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Actions\GenerateProductBarcodeAction;
use Modules\Inventory\Models\Product;

class BarcodeController extends Controller
{
    public function show(Product $product, GenerateProductBarcodeAction $action): JsonResponse
    {
        $barcode = $action->execute($product);

        return response()->json(['barcode' => $barcode]);
    }
}
