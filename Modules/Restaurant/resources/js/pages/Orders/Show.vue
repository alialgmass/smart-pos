<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

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

const props = defineProps<{
    order: OrderData;
}>();

const statusLabel = (status: number) => {
    switch (status) {
        case 1: return 'Open';
        case 2: return 'Sent';
        case 3: return 'Ready';
        case 4: return 'Paid';
        case 5: return 'Cancelled';
        default: return 'Unknown';
    }
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
</script>

<template>
    <Head :title="'Order ' + order.order_number" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Order {{ order.order_number }}</h1>
                <p class="text-sm text-muted-foreground">
                    Table: {{ order.table?.name ?? 'N/A' }} &middot;
                    Server: {{ order.user?.name ?? 'N/A' }} &middot;
                    {{ new Date(order.created_at).toLocaleString() }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Badge :variant="statusVariant(order.status)" class="text-sm">
                    {{ statusLabel(order.status) }}
                </Badge>

                <Button
                    v-if="canSendToKitchen"
                    variant="default"
                    @click="router.post(route('restaurant.orders.send-to-kitchen', order.id))"
                >
                    Send to Kitchen
                </Button>
                <Button
                    v-if="canCheckout"
                    variant="success"
                    @click="router.post(route('restaurant.orders.checkout', order.id))"
                >
                    Checkout
                </Button>
            </div>
        </div>

        <div v-if="order.notes" class="rounded-md bg-muted p-3 text-sm">
            <strong>Notes:</strong> {{ order.notes }}
        </div>

        <div class="rounded-md border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-2 text-left font-medium">Item</th>
                        <th class="px-4 py-2 text-center font-medium">Qty</th>
                        <th class="px-4 py-2 text-right font-medium">Price</th>
                        <th class="px-4 py-2 text-center font-medium">Kitchen</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in order.items"
                        :key="item.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-2">
                            <div>{{ item.name }}</div>
                            <div v-if="item.notes" class="text-xs text-muted-foreground">
                                {{ item.notes }}
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">{{ item.qty }}</td>
                        <td class="px-4 py-2 text-right">${{ item.price }}</td>
                        <td class="px-4 py-2 text-center">
                            <template v-if="item.sent_to_kitchen_at">
                                <Badge variant="success">Sent</Badge>
                            </template>
                            <template v-else>
                                <Badge variant="secondary">Pending</Badge>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="order.items.length === 0" class="py-12 text-center text-muted-foreground">
            No items on this order.
        </div>
    </div>
</template>
