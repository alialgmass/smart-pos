<?php

namespace Modules\Sales\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sales\Enums\SaleStatus;
use Modules\Sales\Models\Sale;
use Modules\Shared\Enums\PaymentMethod;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 1000);
        $discount = 0;
        $tax = round($subtotal * 0.14, 2);
        $total = $subtotal + $tax - $discount;

        return [
            'tenant_id' => 1,
            'user_id' => User::factory(),
            'customer_id' => null,
            'order_id' => null,
            'invoice_number' => 'INV-1-'.now()->timestamp,
            'payment_method' => PaymentMethod::Cash,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
            'paid_amount' => $total,
            'change_amount' => 0,
            'status' => SaleStatus::Completed,
            'offline_local_id' => null,
        ];
    }
}
