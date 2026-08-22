<?php

namespace Modules\Reports\Actions;

use Modules\Reports\Repositories\ProfitReportRepository;

class GenerateProfitReportAction
{
    public function __construct(
        private readonly ProfitReportRepository $profitReport,
    ) {}

    public function execute(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->profitReport->grossProfit($tenantId, $startDate, $endDate);
    }
}
