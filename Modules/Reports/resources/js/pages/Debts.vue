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

interface OutstandingDebt {
    id: number;
    invoice_number: string;
    customer_id: number | null;
    total: string;
    paid_amount: string;
    outstanding: string;
    customer: { id: number; name: string } | null;
}

const props = defineProps<{
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

const agingLabels: Record<string, string> = {
    '1_7': '1-7 days',
    '8_30': '8-30 days',
    '31_90': '31-90 days',
    '91_plus': '91+ days',
};

const agingVariant = (key: string): 'success' | 'warning' | 'destructive' => {
    if (key === '1_7') return 'success';
    if (key === '8_30' || key === '31_90') return 'warning';
    return 'destructive';
};
</script>

<template>
    <Head title="Debt Report" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Debt Report</h1>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div v-for="(count, key) in report.aging" :key="key" class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">{{ agingLabels[key] }}</p>
                <p class="text-2xl font-bold">
                    <Badge :variant="agingVariant(key)">{{ count }}</Badge>
                </p>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="px-4 py-3 border-b">
                <h2 class="font-semibold">Outstanding Debts</h2>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Customer</TableHead>
                        <TableHead>Total</TableHead>
                        <TableHead>Paid</TableHead>
                        <TableHead>Outstanding</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="debt in report.outstanding" :key="debt.id">
                        <TableCell class="font-medium">{{ debt.invoice_number }}</TableCell>
                        <TableCell>{{ debt.customer?.name ?? '-' }}</TableCell>
                        <TableCell>${{ Number(debt.total).toFixed(2) }}</TableCell>
                        <TableCell>${{ Number(debt.paid_amount).toFixed(2) }}</TableCell>
                        <TableCell class="font-semibold text-destructive">${{ Number(debt.outstanding).toFixed(2) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="report.outstanding.length === 0">
                        <TableCell colspan="5" class="text-center text-muted-foreground">No outstanding debts</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
