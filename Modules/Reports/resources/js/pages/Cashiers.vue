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

interface CashierRow {
    user_id: number;
    user_name: string;
    transaction_count: number;
    total_sales: string;
    average_sale: string;
}

const props = defineProps<{
    report: CashierRow[];
    filters: {
        user_id?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const form = useForm({
    user_id: props.filters.user_id ?? '',
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
});

const search = () => {
    form.get(route('reports.cashiers'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Cashier Report" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Cashier Performance</h1>

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
            <div class="grid gap-2">
                <Label for="user_id">User ID</Label>
                <Input id="user_id" type="number" v-model="form.user_id" class="w-24" />
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
                        <TableHead>Cashier</TableHead>
                        <TableHead>Transactions</TableHead>
                        <TableHead>Total Sales</TableHead>
                        <TableHead>Avg Sale</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in report" :key="row.user_id">
                        <TableCell class="font-medium">{{ row.user_name }}</TableCell>
                        <TableCell>{{ row.transaction_count }}</TableCell>
                        <TableCell>${{ Number(row.total_sales).toFixed(2) }}</TableCell>
                        <TableCell>${{ Number(row.average_sale).toFixed(2) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="report.length === 0">
                        <TableCell colspan="4" class="text-center text-muted-foreground">No data found</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
