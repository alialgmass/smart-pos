<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
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
import { cashiers } from '@/routes/reports';
import { Head, useForm } from '@inertiajs/vue3';
import { UserRound } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface CashierRow {
    user_id: number;
    user_name: string;
    transaction_count: number;
    total_sales: string;
    average_sale: string;
}

const { t } = useI18n();

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
    form.get(cashiers.url(), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="t('reports::cashiers.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('reports::cashiers.title')" />

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
            <div class="grid gap-2">
                <Label for="user_id">{{ t('reports::cashiers.user_id') }}</Label>
                <Input id="user_id" v-model="form.user_id" type="number" class="w-24" />
            </div>
            <div class="grid gap-2">
                <Label for="start_date">{{ t('reports::cashiers.start_date') }}</Label>
                <Input id="start_date" v-model="form.start_date" type="date" />
            </div>
            <div class="grid gap-2">
                <Label for="end_date">{{ t('reports::cashiers.end_date') }}</Label>
                <Input id="end_date" v-model="form.end_date" type="date" />
            </div>
            <Button type="submit" :disabled="form.processing">{{ t('reports::cashiers.filter') }}</Button>
        </form>

        <EmptyState
            v-if="report.length === 0"
            :icon="UserRound"
            :title="t('reports::cashiers.no_data')"
        />

        <div v-else class="overflow-hidden rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('reports::cashiers.cashier') }}</TableHead>
                        <TableHead>{{ t('reports::cashiers.transactions') }}</TableHead>
                        <TableHead>{{ t('reports::cashiers.total_sales') }}</TableHead>
                        <TableHead>{{ t('reports::cashiers.avg_sale') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in report" :key="row.user_id">
                        <TableCell class="font-medium">{{ row.user_name }}</TableCell>
                        <TableCell class="tabular-nums">{{ row.transaction_count }}</TableCell>
                        <TableCell class="font-mono tabular-nums">${{ Number(row.total_sales).toFixed(2) }}</TableCell>
                        <TableCell class="font-mono tabular-nums">${{ Number(row.average_sale).toFixed(2) }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
