<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Product {
    product_id: number | null;
    name: string;
    total_qty: string;
    total_revenue: string;
}

const props = defineProps<{
    products: Product[];
    filters: {
        limit?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const form = useForm({
    limit: props.filters.limit ?? '10',
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
});

const search = () => {
    form.get(route('reports.top-products'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Top Products Report" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Top Products</h1>

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
            <div class="grid gap-2">
                <Label for="limit">Limit</Label>
                <Input id="limit" type="number" v-model="form.limit" class="w-24" />
            </div>
            <div class="grid gap-2">
                <Label for="start_date">Start Date</Label>
                <Input id="start_date" type="date" v-model="form.start_date" />
            </div>
            <div class="grid gap-2">
                <Label for="end_date">End Date</Label>
                <Input id="end_date" type="date" v-model="form.end_date" />
            </div>
            <Button type="submit" :disabled="form.processing">Filter</Button>
        </form>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>Product</TableHead>
                        <TableHead>Qty Sold</TableHead>
                        <TableHead>Revenue</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(product, index) in products" :key="product.product_id ?? index">
                        <TableCell>{{ index + 1 }}</TableCell>
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell>{{ Number(product.total_qty).toFixed(0) }}</TableCell>
                        <TableCell>${{ Number(product.total_revenue).toFixed(2) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="products.length === 0">
                        <TableCell colspan="4" class="text-center text-muted-foreground">No data found</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
