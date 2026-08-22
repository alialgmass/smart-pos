<?php

namespace Modules\Reports\Repositories;

use Modules\Sales\Models\SaleItem;

class ProductReportRepository
{
    public function topProducts(int $tenantId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = SaleItem::query()
            ->withoutGlobalScope('tenant')
            ->where('sale_items.tenant_id', $tenantId)
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('sale_items.product_id')
            ->selectRaw('sale_items.name')
            ->selectRaw('SUM(sale_items.qty) as total_qty')
            ->selectRaw('SUM(sale_items.total) as total_revenue')
            ->groupBy('sale_items.product_id', 'sale_items.name')
            ->orderByDesc('total_qty')
            ->limit($limit);

        if ($startDate !== null) {
            $query->whereDate('sales.created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('sales.created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }
}
