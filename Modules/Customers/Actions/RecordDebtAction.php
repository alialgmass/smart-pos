<?php

namespace Modules\Customers\Actions;

use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\Customer;
use Modules\Customers\Models\CustomerDebt;

class RecordDebtAction
{
    public function execute(int $customerId, ?int $saleId, float $amount): CustomerDebt
    {
        $customer = Customer::query()
            ->withoutGlobalScope('tenant')
            ->findOrFail($customerId);

        $customer->increment('debt_balance', $amount);

        return CustomerDebt::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customerId,
            'sale_id' => $saleId,
            'amount' => $amount,
            'paid_amount' => 0,
            'status' => CustomerDebtStatus::Open,
        ]);
    }
}
