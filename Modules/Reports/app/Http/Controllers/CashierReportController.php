<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reports\Actions\GenerateCashierReportAction;

class CashierReportController extends Controller
{
    public function index(GenerateCashierReportAction $action): Response
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $report = $action->execute(
            $tenantId,
            request('user_id') ? (int) request('user_id') : null,
            request('start_date'),
            request('end_date'),
        );

        return Inertia::render('Reports/Cashiers', [
            'report' => $report,
            'filters' => request()->only(['user_id', 'start_date', 'end_date']),
        ]);
    }
}
