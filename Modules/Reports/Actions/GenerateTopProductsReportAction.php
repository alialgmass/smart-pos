<?php

namespace Modules\Reports\Actions;

use Modules\Reports\Repositories\ProductReportRepository;

class GenerateTopProductsReportAction
{
    public function __construct(
        private readonly ProductReportRepository $productReport,
    ) {}

    public function execute(int $tenantId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->productReport->topProducts($tenantId, $limit, $startDate, $endDate);
    }
}
