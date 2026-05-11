<script setup lang="ts">
import { ref } from 'vue';
import { useInstallPrompt } from '../composables/useInstallPrompt';

const { isInstallable, promptInstall } = useInstallPrompt();
const dismissed = ref(false);

async function handleInstall() {
    const accepted = await promptInstall();
    if (accepted) {
        dismissed.value = true;
    }
}
</script>

<template>
    <div
        v-if="isInstallable() && !dismissed"
        class="flex items-center justify-between rounded-lg border bg-blue-50 p-4"
    >
        <div>
            <p class="text-sm font-medium">Install app</p>
            <p class="text-sm text-muted-foreground">
                Install this app on your device for offline access
            </p>
        </div>
        <div class="flex gap-2">
            <button
                class="rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground"
                @click="handleInstall"
            >
                Install
            </button>
            <button
                class="rounded-md border bg-background px-3 py-1.5 text-sm"
                @click="dismissed = true"
            >
                Not now
            </button>
        </div>
    </div>
</template>
