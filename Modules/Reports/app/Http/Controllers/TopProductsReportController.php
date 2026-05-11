<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reports\Actions\GenerateTopProductsReportAction;

class TopProductsReportController extends Controller
{
    public function index(GenerateTopProductsReportAction $action): Response
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $products = $action->execute(
            $tenantId,
            (int) request('limit', 10),
            request('start_date'),
            request('end_date'),
        );

        return Inertia::render('Reports/ProductsTop', [
            'products' => $products,
            'filters' => request()->only(['limit', 'start_date', 'end_date']),
        ]);
    }
}
