import { reactive, ref } from 'vue';

interface OfflineState {
    isOnline: boolean;
    pendingSyncCount: number;
    installPromptEvent: Event | null;
    lastSyncAt: string | null;
}

const state = reactive<OfflineState>({
    isOnline: navigator.onLine,
    pendingSyncCount: 0,
    installPromptEvent: null,
    lastSyncAt: null,
});

export function useOfflineStore() {
    function setOnline(value: boolean) {
        state.isOnline = value;
    }

    function incrementPending(count = 1) {
        state.pendingSyncCount += count;
    }

    function clearPending() {
        state.pendingSyncCount = 0;
    }

    function setInstallPrompt(event: Event | null) {
        state.installPromptEvent = event;
    }

    function setLastSync(timestamp: string) {
        state.lastSyncAt = timestamp;
    }

    return {
        state,
        setOnline,
        incrementPending,
        clearPending,
        setInstallPrompt,
        setLastSync,
    };
}
