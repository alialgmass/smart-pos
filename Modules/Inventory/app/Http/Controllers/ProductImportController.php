<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Actions\ImportProductsAction;
use Modules\Inventory\Http\Requests\ImportProductsRequest;

class ProductImportController extends Controller
{
    public function preview(ImportProductsRequest $request, ImportProductsAction $action): JsonResponse
    {
        $result = $action->preview($request->input('rows', []));

        return response()->json($result);
    }

    public function confirm(ImportProductsRequest $request, ImportProductsAction $action): RedirectResponse
    {
        $preview = $action->preview($request->input('rows', []));

        if (! empty($preview['errors'])) {
            return redirect()->route('inventory.products.index')
                ->with('status', __('Import has errors. Please fix and try again.'));
        }

        $count = $action->confirm($preview['valid']);

        return redirect()->route('inventory.products.index')
            ->with('status', __(':count products imported.', ['count' => $count]));
    }
}
