<?php

namespace Modules\Reports\Actions;

use Modules\Reports\Repositories\DebtReportRepository;

class GenerateDebtReportAction
{
    public function __construct(
        private readonly DebtReportRepository $debtReport,
    ) {}

    public function execute(int $tenantId): array
    {
        return [
            'outstanding' => $this->debtReport->outstandingDebts($tenantId),
            'aging' => $this->debtReport->debtAging($tenantId),
        ];
    }
}
