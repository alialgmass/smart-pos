<?php

namespace Modules\Reports\Actions;

use Modules\Inventory\Models\Product;
use Modules\Reports\Repositories\ProductReportRepository;
use Modules\Reports\Repositories\SalesReportRepository;
use Modules\Sales\Models\Sale;

class GenerateDashboardMetricsAction
{
    public function __construct(
        private readonly SalesReportRepository $salesReport,
        private readonly ProductReportRepository $productReport,
    ) {}

    public function execute(int $tenantId): array
    {
        $todayStats = $this->salesReport->todaySales($tenantId);
        $yesterdayStats = $this->salesReport->todaySales($tenantId, now()->subDay());

        $topProducts = $this->productReport->topProducts($tenantId, 1);

        $lowStockProducts = Product::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereColumn('stock_qty', '<=', 'min_stock')
            ->with('category:id,name')
            ->orderBy('stock_qty')
            ->limit(20)
            ->get(['id', 'name', 'category_id', 'stock_qty', 'min_stock'])
            ->toArray();

        $dailySales30 = Sale::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();

        $endDate = now();
        $dateCursor = (clone $endDate)->subDays(29);
        $salesTrend = [];
        while ($dateCursor <= $endDate) {
            $key = $dateCursor->format('Y-m-d');
            $salesTrend[] = [
                'date' => $key,
                'total' => (float) ($dailySales30[$key]['total'] ?? 0),
            ];
            $dateCursor = $dateCursor->addDay();
        }

        $rawBreakdown = $this->salesReport->salesByPaymentMethod($tenantId);
        $paymentTotal = array_sum(array_column($rawBreakdown, 'total')) ?: 1;
        $paymentBreakdown = array_map(fn ($row) => [
            'payment_method' => $row['payment_method'],
            'count' => (int) $row['count'],
            'total' => (float) $row['total'],
            'percentage' => round(($row['total'] / $paymentTotal) * 100, 1),
        ], $rawBreakdown);

        return [
            'today_sales' => (float) ($todayStats['total_sales'] ?? 0),
            'yesterday_sales' => (float) ($yesterdayStats['total_sales'] ?? 0),
            'yesterday_transactions' => (int) ($yesterdayStats['transaction_count'] ?? 0),
            'yesterday_avg_sale' => (float) ($yesterdayStats['average_sale'] ?? 0),
            'transaction_count' => (int) ($todayStats['transaction_count'] ?? 0),
            'avg_sale' => (float) ($todayStats['average_sale'] ?? 0),
            'top_product' => $topProducts[0] ?? null,
            'low_stock_count' => count($lowStockProducts),
            'low_stock_products' => $lowStockProducts,
            'sales_trend' => $salesTrend,
            'payment_breakdown' => $paymentBreakdown,
        ];
    }
}
