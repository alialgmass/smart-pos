<?php

namespace Modules\Offline\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Offline\Actions\ProcessOfflineSaleAction;
use Modules\Shared\Enums\OfflineSyncStatus;

class OfflineSyncController extends Controller
{
    public function __construct(
        private readonly ProcessOfflineSaleAction $processSale,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sales' => 'required|array',
            'sales.*.offline_local_id' => 'required|string',
            'sales.*.subtotal' => 'nullable|numeric',
            'sales.*.total' => 'nullable|numeric',
            'sales.*.paid_amount' => 'nullable|numeric',
            'sales.*.payment_method' => 'nullable|integer',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $results = [];

        foreach ($data['sales'] as $saleData) {
            $results[] = $this->processSale->execute($saleData, $tenantId);
        }

        return response()->json([
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'synced' => count(array_filter($results, fn ($r) => $r['status'] === OfflineSyncStatus::Ok)),
                'skipped' => count(array_filter($results, fn ($r) => $r['status'] === OfflineSyncStatus::Skipped)),
                'errors' => count(array_filter($results, fn ($r) => $r['status'] === OfflineSyncStatus::Error)),
            ],
        ]);
    }
}
