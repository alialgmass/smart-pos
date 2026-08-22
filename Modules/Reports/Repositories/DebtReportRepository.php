<?php

namespace Modules\Reports\Repositories;

use Modules\Sales\Models\Sale;
use Modules\Shared\Enums\PaymentMethod;

class DebtReportRepository
{
    public function outstandingDebts(int $tenantId): array
    {
        return Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('payment_method', PaymentMethod::Deferred)
            ->whereRaw('paid_amount < total')
            ->selectRaw('id, invoice_number, customer_id, total, paid_amount, (total - paid_amount) as outstanding')
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function debtAging(int $tenantId): array
    {
        $buckets = [
            '1_7' => [1, 7],
            '8_30' => [8, 30],
            '31_90' => [31, 90],
            '91_plus' => [91, null],
        ];

        $aging = [];

        foreach ($buckets as $key => [$min, $max]) {
            $query = Sale::query()
                ->withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('payment_method', PaymentMethod::Deferred)
                ->whereRaw('paid_amount < total')
                ->whereRaw("julianday('now') - julianday(created_at) >= ?", [$min]);

            if ($max !== null) {
                $query->whereRaw("julianday('now') - julianday(created_at) <= ?", [$max]);
            }

            $aging[$key] = $query->count();
        }

        return $aging;
    }
}
