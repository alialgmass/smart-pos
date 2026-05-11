<script setup lang="ts">
import { onMounted, ref } from 'vue';
import InstallAppBanner from '../components/InstallAppBanner.vue';
import { db } from '../lib/db';
import { useOfflineStore } from '../stores/useOfflineStore';
import { useOfflineSync } from '../composables/useOfflineSync';

const store = useOfflineStore();
const { syncing, lastError, syncPendingSales } = useOfflineSync();

const pendingSales = ref(db.offlineSales.getPending());
const syncStatus = ref(db.syncStatus.get());

onMounted(() => {
    pendingSales.value = db.offlineSales.getPending();
    syncStatus.value = db.syncStatus.get();
});

async function handleSync() {
    await syncPendingSales();
    pendingSales.value = db.offlineSales.getPending();
    syncStatus.value = db.syncStatus.get();
}
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <InstallAppBanner />

        <div>
            <h1 class="text-2xl font-bold">Offline mode</h1>
            <p class="text-muted-foreground">Manage offline data and sync status</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-card p-4">
                <dt class="text-sm text-muted-foreground">Connection</dt>
                <dd class="mt-1 text-lg font-semibold" :class="store.state.isOnline ? 'text-green-600' : 'text-red-600'">
                    {{ store.state.isOnline ? 'Online' : 'Offline' }}
                </dd>
            </div>

            <div class="rounded-lg border bg-card p-4">
                <dt class="text-sm text-muted-foreground">Pending sync</dt>
                <dd class="mt-1 text-lg font-semibold">{{ pendingSales.length }}</dd>
            </div>

            <div class="rounded-lg border bg-card p-4">
                <dt class="text-sm text-muted-foreground">Last sync</dt>
                <dd class="mt-1 text-lg font-semibold">
                    {{ syncStatus.last_sync_at ? new Date(syncStatus.last_sync_at).toLocaleString() : 'Never' }}
                </dd>
            </div>
        </div>

        <div v-if="lastError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            {{ lastError }}
        </div>

        <button
            v-if="pendingSales.length > 0"
            :disabled="syncing"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
            @click="handleSync"
        >
            {{ syncing ? 'Syncing...' : `Sync ${pendingSales.length} pending sale(s)` }}
        </button>

        <div v-if="pendingSales.length > 0" class="rounded-lg border bg-card">
            <div class="border-b px-4 py-3 font-medium">Pending sales</div>
            <ul class="divide-y">
                <li
                    v-for="sale in pendingSales"
                    :key="sale.offline_local_id"
                    class="flex items-center justify-between px-4 py-3 text-sm"
                >
                    <span>{{ sale.offline_local_id }}</span>
                    <span>${{ sale.total.toFixed(2) }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>
