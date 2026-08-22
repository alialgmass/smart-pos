<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as indexRoute } from '@/routes/sales';

interface Sale {
    id: number;
    invoice_number: string;
    total: string;
    payment_method: number;
    status: number;
    created_at: string;
    user: { id: number; name: string } | null;
    items_count?: number;
}

const props = defineProps<{
    sales: {
        data: Sale[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const paymentLabels: Record<number, string> = {
    1: 'Cash',
    2: 'Card',
    3: 'Mixed',
    4: 'Deferred',
};

const statusLabels: Record<number, string> = {
    1: 'Completed',
    2: 'Refunded',
    3: 'Partial Refund',
};

const statusVariant = (status: number) => {
    switch (status) {
        case 1: return 'success' as const;
        case 2: return 'destructive' as const;
        case 3: return 'warning' as const;
        default: return 'outline' as const;
    }
};
</script>

<template>
    <Head title="Sales" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Sales History</h1>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Cashier</TableHead>
                        <TableHead>Payment</TableHead>
                        <TableHead class="text-right">Total</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="sale in sales.data" :key="sale.id">
                        <TableCell class="font-medium">{{ sale.invoice_number }}</TableCell>
                        <TableCell class="text-sm">{{ new Date(sale.created_at).toLocaleDateString() }}</TableCell>
                        <TableCell class="text-sm">{{ sale.user?.name ?? '—' }}</TableCell>
                        <TableCell class="text-sm">{{ paymentLabels[sale.payment_method] ?? '—' }}</TableCell>
                        <TableCell class="text-right font-mono">${{ Number(sale.total).toFixed(2) }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(sale.status)">{{ statusLabels[sale.status] ?? 'Unknown' }}</Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <Button variant="outline" size="sm">View</Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="sales.data.length === 0">
                        <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                            No sales yet
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-if="sales.last_page > 1" class="flex justify-center gap-2">
            <Link
                v-for="page in sales.last_page"
                :key="page"
                :href="indexRoute.url({ query: { page } })"
                class="rounded-md px-3 py-1 text-sm"
                :class="page === sales.current_page ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
