<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface TopProduct {
    product_id: number | null;
    name: string;
    total_qty: string;
    total_revenue: string;
}

const props = defineProps<{
    metrics: {
        today_sales: number;
        transaction_count: number;
        avg_sale: number;
        top_product: TopProduct | null;
        low_stock_count: number;
    };
}>();
</script>

<template>
    <Head title="Reports Dashboard" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Today's Sales</p>
                <p class="text-2xl font-bold">${{ Number(metrics.today_sales).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Transactions</p>
                <p class="text-2xl font-bold">{{ metrics.transaction_count }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Avg Sale</p>
                <p class="text-2xl font-bold">${{ Number(metrics.avg_sale).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Top Product</p>
                <p class="text-lg font-bold truncate">{{ metrics.top_product?.name ?? 'N/A' }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Low Stock Items</p>
                <p class="text-2xl font-bold">
                    <Badge :variant="metrics.low_stock_count > 0 ? 'warning' : 'success'">
                        {{ metrics.low_stock_count }}
                    </Badge>
                </p>
            </div>
        </div>

        <div v-if="metrics.top_product" class="rounded-md border">
            <div class="px-4 py-3 border-b">
                <h2 class="font-semibold">Top Product Details</h2>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Qty Sold</TableHead>
                        <TableHead>Revenue</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow>
                        <TableCell class="font-medium">{{ metrics.top_product.name }}</TableCell>
                        <TableCell>{{ Number(metrics.top_product.total_qty).toFixed(0) }}</TableCell>
                        <TableCell>${{ Number(metrics.top_product.total_revenue).toFixed(2) }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
