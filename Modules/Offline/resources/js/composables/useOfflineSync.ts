import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { db } from '../lib/db';
import { useOfflineStore } from '../stores/useOfflineStore';

export function useOfflineSync() {
    const syncing = ref(false);
    const lastError = ref<string | null>(null);
    const store = useOfflineStore();

    async function syncPendingSales() {
        const pending = db.offlineSales.getPending();

        if (pending.length === 0) {
            return;
        }

        syncing.value = true;
        lastError.value = null;

        try {
            const response = await fetch('/api/v1/offline/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ sales: pending }),
            });

            if (!response.ok) {
                throw new Error(`Sync failed: ${response.statusText}`);
            }

            const result = await response.json();

            for (const r of result.results ?? []) {
                if (r.status === 1) {
                    db.offlineSales.markSynced(r.offline_local_id);
                }
            }

            const timestamp = new Date().toISOString();
            db.syncStatus.updateLastSync(timestamp);
            store.setLastSync(timestamp);
            store.clearPending();
        } catch (e) {
            const message = e instanceof Error ? e.message : 'Sync failed';
            lastError.value = message;
        } finally {
            syncing.value = false;
        }
    }

    function getCsrfToken(): string {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta?.getAttribute('content') ?? '';
    }

    return {
        syncing,
        lastError,
        syncPendingSales,
    };
}
