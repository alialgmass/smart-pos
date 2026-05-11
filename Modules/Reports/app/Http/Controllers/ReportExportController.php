<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Reports\Exports\ReportExport;

class ReportExportController extends Controller
{
    public function download(ReportExport $export): Response
    {
        $data = request()->validate([
            'title' => ['required', 'string', 'max:255'],
            'rows' => ['required', 'array'],
            'headers' => ['required', 'array'],
            'date_range' => ['required', 'string'],
        ]);

        $tenantName = auth()->user()->tenant->name ?? '';

        $csv = $export->generate(
            $data['title'],
            $data['rows'],
            $data['headers'],
            $data['date_range'],
            $tenantName,
        );

        $filename = str_replace(' ', '_', $data['title']).'_'.now()->format('Ymd_His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
