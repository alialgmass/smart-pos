<?php

namespace Modules\Sales\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\Sales\Models\Sale;

class ApplyDiscountAction
{
    /**
     * Apply a discount to a sale after checking permissions and validation.
     *
     * @throws \RuntimeException
     */
    public function execute(Sale $sale, float $discountAmount, User $user): void
    {
        Gate::forUser($user)->authorize('pos.discount');

        $maxDiscount = $sale->subtotal * 0.5;

        if ($discountAmount > $maxDiscount) {
            throw new \RuntimeException(
                __('Discount cannot exceed 50% of subtotal (:max).', ['max' => number_format($maxDiscount, 2)])
            );
        }

        $sale->update([
            'discount_amount' => $discountAmount,
            'total' => $sale->subtotal + $sale->tax_amount - $discountAmount,
        ]);
    }
}
