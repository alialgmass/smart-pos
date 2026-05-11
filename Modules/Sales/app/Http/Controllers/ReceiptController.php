<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Sales\Models\Sale;

class ReceiptController extends Controller
{
    public function show(int $saleId): View
    {
        $sale = Sale::query()
            ->with('items')
            ->findOrFail($saleId);

        return view('sales::receipts.show', [
            'sale' => $sale,
        ]);
    }
}
