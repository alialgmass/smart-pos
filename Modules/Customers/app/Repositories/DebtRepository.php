<?php

namespace Modules\Customers\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\CustomerDebt;

class DebtRepository
{
    /**
     * @return Collection<int, CustomerDebt>
     */
    public function getPendingDebts(int $tenantId): Collection
    {
        return CustomerDebt::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [CustomerDebtStatus::Open, CustomerDebtStatus::PartialPaid])
            ->with('customer:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, CustomerDebt>
     */
    public function getCustomerDebts(int $customerId): Collection
    {
        return CustomerDebt::query()
            ->where('customer_id', $customerId)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
