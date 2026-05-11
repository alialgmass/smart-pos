<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reports\Actions\GenerateDebtReportAction;

class DebtReportController extends Controller
{
    public function index(GenerateDebtReportAction $action): Response
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $report = $action->execute($tenantId);

        return Inertia::render('Reports/Debts', [
            'report' => $report,
        ]);
    }
}
