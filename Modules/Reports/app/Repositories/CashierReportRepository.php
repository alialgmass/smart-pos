<?php

namespace Modules\Reports\Repositories;

use Modules\Sales\Models\Sale;

class CashierReportRepository
{
    public function cashierPerformance(int $tenantId, ?int $userId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('sales.tenant_id', $tenantId)
            ->selectRaw('sales.user_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('COALESCE(SUM(sales.total), 0) as total_sales')
            ->selectRaw('COALESCE(AVG(sales.total), 0) as average_sale')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->groupBy('sales.user_id', 'users.name')
            ->orderByDesc('total_sales');

        if ($userId !== null) {
            $query->where('sales.user_id', $userId);
        }

        if ($startDate !== null) {
            $query->whereDate('sales.created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('sales.created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }
}
