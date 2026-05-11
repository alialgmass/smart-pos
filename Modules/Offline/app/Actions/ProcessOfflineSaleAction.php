<?php

namespace Modules\Offline\Actions;

use Modules\Sales\Models\Sale;
use Modules\Shared\Enums\OfflineSyncStatus;

class ProcessOfflineSaleAction
{
    public function execute(array $saleData, int $tenantId): array
    {
        $offlineLocalId = $saleData['offline_local_id'] ?? null;

        if ($offlineLocalId === null) {
            return [
                'offline_local_id' => null,
                'status' => OfflineSyncStatus::Error,
                'message' => 'offline_local_id is required',
            ];
        }

        $existing = Sale::query()
            ->where('tenant_id', $tenantId)
            ->where('offline_local_id', $offlineLocalId)
            ->exists();

        if ($existing) {
            return [
                'offline_local_id' => $offlineLocalId,
                'status' => OfflineSyncStatus::Skipped,
                'message' => 'Already synced',
            ];
        }

        Sale::create([
            'tenant_id' => $tenantId,
            'user_id' => $saleData['user_id'] ?? auth()->id(),
            'customer_id' => $saleData['customer_id'] ?? null,
            'invoice_number' => $saleData['invoice_number'] ?? 'OFF-'.$tenantId.'-'.now()->timestamp,
            'payment_method' => $saleData['payment_method'] ?? 1,
            'subtotal' => $saleData['subtotal'] ?? 0,
            'discount_amount' => $saleData['discount_amount'] ?? 0,
            'tax_amount' => $saleData['tax_amount'] ?? 0,
            'total' => $saleData['total'] ?? 0,
            'paid_amount' => $saleData['paid_amount'] ?? 0,
            'change_amount' => $saleData['change_amount'] ?? 0,
            'status' => $saleData['status'] ?? 1,
            'offline_local_id' => $offlineLocalId,
        ]);

        return [
            'offline_local_id' => $offlineLocalId,
            'status' => OfflineSyncStatus::Ok,
            'message' => 'Synced successfully',
        ];
    }
}
