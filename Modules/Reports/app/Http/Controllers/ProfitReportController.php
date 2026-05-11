<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reports\Actions\GenerateProfitReportAction;

class ProfitReportController extends Controller
{
    public function index(GenerateProfitReportAction $action): Response
    {
        Gate::authorize('reports.profit');

        $tenantId = (int) auth()->user()->tenant_id;

        $report = $action->execute(
            $tenantId,
            request('start_date'),
            request('end_date'),
        );

        return Inertia::render('Reports/Profit', [
            'report' => $report,
            'filters' => request()->only(['start_date', 'end_date']),
        ]);
    }
}
