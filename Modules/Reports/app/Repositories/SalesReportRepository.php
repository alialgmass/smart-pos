<?php

namespace Modules\Reports\Repositories;

use Modules\Sales\Models\Sale;

class SalesReportRepository
{
    public function todaySales(int $tenantId): array
    {
        return Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('COALESCE(AVG(total), 0) as average_sale')
            ->first()
            ?->toArray() ?? [];
    }

    public function monthlySales(int $tenantId, int $month, int $year): array
    {
        return Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
            ->selectRaw('COUNT(*) as transaction_count')
            ->first()
            ?->toArray() ?? [];
    }

    public function salesByPaymentMethod(int $tenantId): array
    {
        return Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->select('payment_method')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}
