import { onMounted, onUnmounted } from 'vue';
import { useOfflineStore } from '../stores/useOfflineStore';

export function useInstallPrompt() {
    const store = useOfflineStore();

    let handler: EventListener;

    onMounted(() => {
        handler = (e: Event) => {
            e.preventDefault();
            store.setInstallPrompt(e);
        };

        window.addEventListener('beforeinstallprompt', handler);
    });

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', handler);
    });

    async function promptInstall(): Promise<boolean> {
        const event = store.state.installPromptEvent as BeforeInstallPromptEvent | null;

        if (event === null) {
            return false;
        }

        event.prompt();

        const result = await event.userChoice;

        store.setInstallPrompt(null);

        return result.outcome === 'accepted';
    }

    return {
        isInstallable: () => store.state.installPromptEvent !== null,
        promptInstall,
    };
}

interface BeforeInstallPromptEvent extends Event {
    prompt: () => void;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}
