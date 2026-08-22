<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

interface OutstandingDebt {
    id: number;
    invoice_number: string;
    customer_id: number | null;
    total: string;
    paid_amount: string;
    outstanding: string;
    customer: { id: number; name: string } | null;
}

const { t } = useI18n();

defineProps<{
    report: {
        outstanding: OutstandingDebt[];
        aging: {
            '1_7': number;
            '8_30': number;
            '31_90': number;
            '91_plus': number;
        };
    };
}>();

const agingKeys = ['1_7', '8_30', '31_90', '91_plus'] as const;

const agingLabel = (key: string): string => t(`reports::debts_report.aging_${key}`);

const agingVariant = (key: string): 'success' | 'warning' | 'destructive' => {
    if (key === '1_7') {
return 'success';
}

    if (key === '8_30' || key === '31_90') {
return 'warning';
}

    return 'destructive';
};
</script>

<template>
    <Head :title="t('reports::debts_report.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('reports::debts_report.title')" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div v-for="key in agingKeys" :key="key" class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">{{ agingLabel(key) }}</p>
                <p class="mt-2 text-2xl font-bold">
                    <Badge :variant="agingVariant(key)">{{ report.aging[key] }}</Badge>
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold">{{ t('reports::debts_report.outstanding_debts') }}</h2>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('reports::debts_report.invoice') }}</TableHead>
                        <TableHead>{{ t('reports::debts_report.customer') }}</TableHead>
                        <TableHead>{{ t('reports::debts_report.total') }}</TableHead>
                        <TableHead>{{ t('reports::debts_report.paid') }}</TableHead>
                        <TableHead>{{ t('reports::debts_report.outstanding') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="debt in report.outstanding" :key="debt.id">
                        <TableCell class="font-medium">{{ debt.invoice_number }}</TableCell>
                        <TableCell>{{ debt.customer?.name ?? '-' }}</TableCell>
                        <TableCell class="font-mono tabular-nums">${{ Number(debt.total).toFixed(2) }}</TableCell>
                        <TableCell class="font-mono tabular-nums">${{ Number(debt.paid_amount).toFixed(2) }}</TableCell>
                        <TableCell class="font-mono font-semibold tabular-nums text-destructive">${{ Number(debt.outstanding).toFixed(2) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="report.outstanding.length === 0">
                        <TableCell colspan="5" class="text-center text-muted-foreground">{{ t('reports::debts_report.none') }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
