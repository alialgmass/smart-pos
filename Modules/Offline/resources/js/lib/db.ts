interface OfflineSale {
    offline_local_id: string;
    subtotal: number;
    total: number;
    paid_amount: number;
    payment_method: number;
    customer_id?: number | null;
    status?: number;
    synced: boolean;
    created_at: string;
}

interface SyncStatus {
    last_sync_at: string | null;
    pending_count: number;
}

const DB_KEY = 'offline_pos_db';

function getDb<T>(key: string, fallback: T): T {
    try {
        const raw = localStorage.getItem(`${DB_KEY}_${key}`);
        return raw !== null ? (JSON.parse(raw) as T) : fallback;
    } catch {
        return fallback;
    }
}

function setDb<T>(key: string, value: T): void {
    try {
        localStorage.setItem(`${DB_KEY}_${key}`, JSON.stringify(value));
    } catch {
        // storage full or unavailable
    }
}

export const db = {
    products: {
        getAll: () => getDb<unknown[]>('products', []),
        setAll: (items: unknown[]) => setDb('products', items),
    },

    categories: {
        getAll: () => getDb<unknown[]>('categories', []),
        setAll: (items: unknown[]) => setDb('categories', items),
    },

    customers: {
        getAll: () => getDb<unknown[]>('customers', []),
        setAll: (items: unknown[]) => setDb('customers', items),
    },

    offlineSales: {
        getAll: () => getDb<OfflineSale[]>('offline_sales', []),

        add: (sale: OfflineSale) => {
            const sales = getDb<OfflineSale[]>('offline_sales', []);
            sales.push(sale);
            setDb('offline_sales', sales);
        },

        remove: (offlineLocalId: string) => {
            const sales = getDb<OfflineSale[]>('offline_sales', []);
            setDb('offline_sales', sales.filter((s) => s.offline_local_id !== offlineLocalId));
        },

        markSynced: (offlineLocalId: string) => {
            const sales = getDb<OfflineSale[]>('offline_sales', []);
            const idx = sales.findIndex((s) => s.offline_local_id === offlineLocalId);
            if (idx !== -1) {
                sales[idx].synced = true;
                setDb('offline_sales', sales);
            }
        },

        getPending: () =>
            getDb<OfflineSale[]>('offline_sales', []).filter((s) => !s.synced),
    },

    syncStatus: {
        get: () => getDb<SyncStatus>('sync_status', { last_sync_at: null, pending_count: 0 }),

        set: (status: SyncStatus) => setDb('sync_status', status),

        updateLastSync: (timestamp: string) => {
            const current = getDb<SyncStatus>('sync_status', {
                last_sync_at: null,
                pending_count: 0,
            });
            current.last_sync_at = timestamp;
            current.pending_count = getDb<OfflineSale[]>('offline_sales', []).filter((s) => !s.synced).length;
            setDb('sync_status', current);
        },
    },

    clear: () => {
        const keys = Object.keys(localStorage).filter((k) => k.startsWith(DB_KEY));
        keys.forEach((k) => localStorage.removeItem(k));
    },
};
