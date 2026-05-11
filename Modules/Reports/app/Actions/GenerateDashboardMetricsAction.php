<?php

namespace Modules\Reports\Actions;

use Modules\Inventory\Models\Product;
use Modules\Reports\Repositories\ProductReportRepository;
use Modules\Reports\Repositories\SalesReportRepository;

class GenerateDashboardMetricsAction
{
    public function __construct(
        private readonly SalesReportRepository $salesReport,
        private readonly ProductReportRepository $productReport,
    ) {}

    public function execute(int $tenantId): array
    {
        $todayStats = $this->salesReport->todaySales($tenantId);

        $topProducts = $this->productReport->topProducts($tenantId, 1);

        $lowStockCount = Product::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereColumn('stock_qty', '<=', 'min_stock')
            ->count();

        return [
            'today_sales' => $todayStats['total_sales'] ?? 0,
            'transaction_count' => $todayStats['transaction_count'] ?? 0,
            'avg_sale' => $todayStats['average_sale'] ?? 0,
            'top_product' => $topProducts[0] ?? null,
            'low_stock_count' => $lowStockCount,
        ];
    }
}
