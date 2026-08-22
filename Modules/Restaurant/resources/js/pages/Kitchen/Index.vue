<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router } from '@inertiajs/vue3';
import { ChefHat, Printer } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface OrderItem {
    id: number;
    name: string;
    qty: string;
    notes: string | null;
    sent_to_kitchen_at: string | null;
}

interface KitchenOrder {
    id: number;
    order_number: string;
    status: number;
    notes: string | null;
    created_at: string;
    table: { id: number; name: string } | null;
    items: OrderItem[];
}

const { t, te } = useI18n();

defineProps<{
    orders: KitchenOrder[];
}>();

const statusLabel = (status: number): string => {
    if (status === 2 && te('restaurant::kitchen.status_sent')) {
return t('restaurant::kitchen.status_sent');
}

    if (status === 3 && te('restaurant::kitchen.status_ready')) {
return t('restaurant::kitchen.status_ready');
}

    return t('restaurant::kitchen.status_ready');
};

const statusVariant = (status: number) => {
    switch (status) {
        case 2: return 'warning' as const;
        case 3: return 'success' as const;
        default: return 'outline' as const;
    }
};

const markReady = (orderId: number) => {
    router.post(route('restaurant.kitchen.mark-ready', orderId));
};

const printTicket = (orderId: number) => {
    window.open(route('restaurant.kitchen.ticket', orderId), '_blank', 'width=400,height=600');
};
</script>

<template>
    <Head :title="t('restaurant::kitchen.title')" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <PageHeader :title="t('restaurant::kitchen.title')" />
            <Badge variant="outline" class="text-sm">
                {{ t('restaurant::kitchen.pending_count', { count: orders.length }) }}
            </Badge>
        </div>

        <EmptyState
            v-if="orders.length === 0"
            :icon="ChefHat"
            :title="t('restaurant::kitchen.all_caught_up_title')"
            :description="t('restaurant::kitchen.all_caught_up_description')"
        />

        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="order in orders"
                :key="order.id"
                class="transition-shadow hover:shadow-md"
                :class="order.status === 3 ? 'border-emerald-500' : 'border-amber-500'"
            >
                <CardHeader class="pb-2">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg">
                            {{ order.order_number }}
                        </CardTitle>
                        <Badge :variant="statusVariant(order.status)">
                            {{ statusLabel(order.status) }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('restaurant::kitchen.table') }}:
                        {{ order.table?.name ?? '—' }} &middot;
                        {{ new Date(order.created_at).toLocaleTimeString() }}
                    </p>
                </CardHeader>
                <CardContent>
                    <div v-if="order.notes" class="mb-2 rounded bg-muted p-2 text-sm italic">
                        {{ order.notes }}
                    </div>

                    <ul class="divide-y text-sm">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between py-1"
                        >
                            <span>
                                <span class="font-medium tabular-nums">{{ item.qty }}×</span>
                                {{ item.name }}
                                <span v-if="item.notes" class="block text-xs text-muted-foreground italic">
                                    {{ item.notes }}
                                </span>
                            </span>
                        </li>
                    </ul>

                    <div class="mt-3 flex gap-2">
                        <Button
                            v-if="order.status === 2"
                            size="sm"
                            class="flex-1"
                            @click="markReady(order.id)"
                        >
                            {{ t('restaurant::kitchen.mark_ready') }}
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="printTicket(order.id)"
                        >
                            <Printer class="size-4" />
                            {{ t('restaurant::kitchen.print') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
