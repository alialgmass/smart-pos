<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Send, CreditCard } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface OrderItem {
    id: number;
    name: string;
    price: string;
    qty: string;
    notes: string | null;
    sent_to_kitchen_at: string | null;
}

interface OrderData {
    id: number;
    order_number: string;
    status: number;
    notes: string | null;
    created_at: string;
    table: { id: number; name: string } | null;
    user: { id: number; name: string } | null;
    items: OrderItem[];
}

const { t, te } = useI18n();

const props = defineProps<{
    order: OrderData;
}>();

const statusLabel = (status: number): string => {
    const map: Record<number, string> = {
        1: 'restaurant::orders.open',
        2: 'restaurant::orders.sent',
        3: 'restaurant::orders.ready',
        4: 'restaurant::orders.paid',
        5: 'restaurant::orders.cancelled',
    };

    return te(map[status]) ? t(map[status]) : t('restaurant::orders.open');
};

const statusVariant = (status: number) => {
    switch (status) {
        case 1: return 'secondary' as const;
        case 2: return 'warning' as const;
        case 3: return 'success' as const;
        case 4: return 'default' as const;
        case 5: return 'destructive' as const;
        default: return 'outline' as const;
    }
};

const canSendToKitchen = props.order.status === 1;
const canCheckout = props.order.status === 2 || props.order.status === 3;

const sendToKitchen = (): void => {
    router.post(route('restaurant.orders.send-to-kitchen', props.order.id));
};

const checkout = (): void => {
    router.post(route('restaurant.orders.checkout', props.order.id));
};
</script>

<template>
    <Head :title="t('restaurant::orders.title', { number: order.order_number })" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <PageHeader :title="t('restaurant::orders.title', { number: order.order_number })" />
                <p class="-mt-4 text-sm text-muted-foreground">
                    {{ t('restaurant::kitchen.table') }}: {{ order.table?.name ?? t('restaurant::orders.not_available') }} &middot;
                    {{ t('restaurant::orders.server') }}: {{ order.user?.name ?? t('restaurant::orders.not_available') }} &middot;
                    {{ new Date(order.created_at).toLocaleString() }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Badge :variant="statusVariant(order.status)" class="text-sm">
                    {{ statusLabel(order.status) }}
                </Badge>

                <Button
                    v-if="canSendToKitchen"
                    @click="sendToKitchen"
                >
                    <Send class="size-4" />
                    {{ t('restaurant::orders.send_to_kitchen') }}
                </Button>
                <Button
                    v-if="canCheckout"
                    class="bg-emerald-600 text-white hover:bg-emerald-600/90"
                    @click="checkout"
                >
                    <CreditCard class="size-4" />
                    {{ t('restaurant::orders.checkout') }}
                </Button>
            </div>
        </div>

        <div v-if="order.notes" class="rounded-lg bg-muted p-4 text-sm">
            <strong>{{ t('restaurant::orders.notes') }}</strong> {{ order.notes }}
        </div>

        <EmptyState
            v-if="order.items.length === 0"
            :icon="ClipboardList"
            :title="t('restaurant::orders.no_items')"
        />

        <div v-else class="overflow-hidden rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-2.5 text-start font-medium">{{ t('restaurant::orders.item') }}</th>
                        <th class="px-4 py-2.5 text-center font-medium">{{ t('restaurant::orders.qty') }}</th>
                        <th class="px-4 py-2.5 text-end font-medium">{{ t('restaurant::orders.price') }}</th>
                        <th class="px-4 py-2.5 text-center font-medium">{{ t('restaurant::orders.kitchen') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in order.items"
                        :key="item.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-2.5">
                            <div>{{ item.name }}</div>
                            <div v-if="item.notes" class="text-xs text-muted-foreground">
                                {{ item.notes }}
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-center tabular-nums">{{ item.qty }}</td>
                        <td class="px-4 py-2.5 text-end font-mono tabular-nums">${{ Number(item.price).toFixed(2) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <Badge v-if="item.sent_to_kitchen_at" variant="success">
                                {{ t('restaurant::orders.kitchen_sent') }}
                            </Badge>
                            <Badge v-else variant="secondary">
                                {{ t('restaurant::orders.kitchen_pending') }}
                            </Badge>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
