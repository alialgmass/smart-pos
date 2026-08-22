<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { profit } from '@/routes/reports';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
    form.get(profit.url(), {
        preserveState: true,
        preserveScroll: true,
    });
};

const marginPercent = (): string => {
    if (props.report.total_sales === 0) {
return '0.00';
}

    return ((props.report.gross_profit / props.report.total_sales) * 100).toFixed(2);
};
</script>

<template>
    <Head :title="t('reports::profit.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('reports::profit.title')" />

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
            <div class="grid gap-2">
                <Label for="start_date">{{ t('reports::profit.start_date') }}</Label>
                <Input id="start_date" v-model="form.start_date" type="date" />
            </div>
            <div class="grid gap-2">
                <Label for="end_date">{{ t('reports::profit.end_date') }}</Label>
                <Input id="end_date" v-model="form.end_date" type="date" />
            </div>
            <Button type="submit" :disabled="form.processing">{{ t('reports::profit.filter') }}</Button>
        </form>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">{{ t('reports::profit.total_sales') }}</p>
                <p class="mt-1 font-mono text-2xl font-bold tabular-nums">${{ Number(report.total_sales).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">{{ t('reports::profit.total_cost') }}</p>
                <p class="mt-1 font-mono text-2xl font-bold tabular-nums">${{ Number(report.total_cost).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">{{ t('reports::profit.gross_profit') }}</p>
                <p class="mt-1 font-mono text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">${{ Number(report.gross_profit).toFixed(2) }}</p>
            </div>
        </div>

        <div class="rounded-lg border p-4">
            <p class="text-sm text-muted-foreground">{{ t('reports::profit.profit_margin') }}</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ marginPercent() }}%</p>
        </div>
    </div>
</template>
