<?php

namespace Modules\Reports\Repositories;

use Modules\Sales\Models\Sale;

class ProfitReportRepository
{
    public function grossProfit(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('sales.tenant_id', $tenantId)
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('COALESCE(SUM(sales.total), 0) as total_sales')
            ->selectRaw('COALESCE(SUM(sale_items.cost * sale_items.qty), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(sales.total - (sale_items.cost * sale_items.qty)), 0) as gross_profit');

        if ($startDate !== null) {
            $query->whereDate('sales.created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('sales.created_at', '<=', $endDate);
        }

        $result = $query->first();

        if ($result === null) {
            return [
                'total_sales' => 0,
                'total_cost' => 0,
                'gross_profit' => 0,
            ];
        }

        return [
            'total_sales' => (float) $result->total_sales,
            'total_cost' => (float) $result->total_cost,
            'gross_profit' => (float) $result->gross_profit,
        ];
    }
}
