<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Sales\Models\Sale;

class DiscountController extends Controller
{
    /**
     * Preview a discount without applying it.
     */
    public function preview(): JsonResponse
    {
        Gate::authorize('pos.discount');

        $data = request()->validate([
            'sale_id' => 'required|integer|exists:sales,id',
            'discount_amount' => 'required|numeric|min:0',
        ]);

        $sale = Sale::query()->findOrFail($data['sale_id']);

        $maxDiscount = $sale->subtotal * 0.5;

        if ($data['discount_amount'] > $maxDiscount) {
            return response()->json([
                'valid' => false,
                'message' => __('Discount cannot exceed 50% of subtotal (:max).', [
                    'max' => number_format($maxDiscount, 2),
                ]),
                'max_discount' => $maxDiscount,
            ]);
        }

        $newTotal = $sale->subtotal + $sale->tax_amount - $data['discount_amount'];

        return response()->json([
            'valid' => true,
            'new_total' => round($newTotal, 2),
            'discount_amount' => $data['discount_amount'],
            'max_discount' => $maxDiscount,
        ]);
    }
}
