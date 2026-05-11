<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    report: {
        total_sales: number;
        total_cost: number;
        gross_profit: number;
    };
    filters: {
        start_date?: string;
        end_date?: string;
    };
}>();

const form = useForm({
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
});

const search = () => {
    form.get(route('reports.profit'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const marginPercent = (): string => {
    if (props.report.total_sales === 0) return '0.00';
    return ((props.report.gross_profit / props.report.total_sales) * 100).toFixed(2);
};
</script>

<template>
    <Head title="Profit Report" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Profit Report</h1>

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Total Sales</p>
                <p class="text-2xl font-bold">${{ Number(report.total_sales).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Total Cost</p>
                <p class="text-2xl font-bold">${{ Number(report.total_cost).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Gross Profit</p>
                <p class="text-2xl font-bold">${{ Number(report.gross_profit).toFixed(2) }}</p>
            </div>
        </div>

        <div class="rounded-lg border p-4">
            <p class="text-sm text-muted-foreground">Profit Margin</p>
            <p class="text-2xl font-bold">{{ marginPercent() }}%</p>
        </div>
    </div>
</template>
