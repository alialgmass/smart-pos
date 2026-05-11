<?php

namespace Modules\Reports\Actions;

use Modules\Reports\Repositories\CashierReportRepository;

class GenerateCashierReportAction
{
    public function __construct(
        private readonly CashierReportRepository $cashierReport,
    ) {}

    public function execute(int $tenantId, ?int $userId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->cashierReport->cashierPerformance($tenantId, $userId, $startDate, $endDate);
    }
}
