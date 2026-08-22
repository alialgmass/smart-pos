<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Actions\RecordPurchaseAction;
use Modules\Inventory\Http\Requests\PurchaseOrderRequest;

class PurchaseOrderController extends Controller
{
    public function store(PurchaseOrderRequest $request, RecordPurchaseAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->back()->with('status', __('Purchase order created.'));
    }
}
