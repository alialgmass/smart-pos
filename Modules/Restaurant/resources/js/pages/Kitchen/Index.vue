<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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

const props = defineProps<{
    orders: KitchenOrder[];
}>();

const statusLabel = (status: number) => {
    switch (status) {
        case 2: return 'Sent';
        case 3: return 'Ready';
        default: return 'Unknown';
    }
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
    <Head title="Kitchen Display" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Kitchen Display</h1>
            <Badge variant="outline" class="text-sm">
                {{ orders.length }} pending
            </Badge>
        </div>

        <div v-if="orders.length === 0" class="py-12 text-center text-muted-foreground">
            <p class="text-lg">All caught up!</p>
            <p class="text-sm">No pending orders in the kitchen.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="order in orders"
                :key="order.id"
                :class="order.status === 3 ? 'border-green-500' : 'border-amber-500'"
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
                        Table: {{ order.table?.name ?? 'N/A' }} &middot;
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
                                <span class="font-medium">{{ item.qty }}x</span>
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
                            Mark Ready
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="printTicket(order.id)"
                        >
                            Print
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
