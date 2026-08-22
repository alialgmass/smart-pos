<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PaginationLinks from '@/components/shared/PaginationLinks.vue';
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
import { Head } from '@inertiajs/vue3';
import { ReceiptText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

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

const { t, te } = useI18n();

const props = defineProps<{
    sales: {
        data: Sale[];
        current_page: number;
        last_page: number;
        per_page?: number;
        total?: number;
    };
}>();

const paymentLabel = (method: number): string => {
    const key = `sales::sales.payment_${['cash', 'card', 'mixed', 'deferred'][method - 1]}`;

    return te(key) ? t(key) : '—';
};

const statusLabel = (status: number): string => {
    const map: Record<number, string> = {
        1: 'sales::sales.status_completed',
        2: 'sales::sales.status_refunded',
        3: 'sales::sales.status_partial_refund',
    };
    const key = map[status];

    return key && te(key) ? t(key) : t('sales::sales.unknown');
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
    <Head :title="t('sales::sales.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            :title="t('sales::sales.title')"
            :description="t('sales::sales.subtitle')"
        />

        <EmptyState
            v-if="props.sales.data.length === 0"
            :icon="ReceiptText"
            :title="t('sales::sales.no_sales_title')"
            :description="t('sales::sales.no_sales_description')"
        />

        <div v-else class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('sales::sales.invoice') }}</TableHead>
                        <TableHead>{{ t('sales::sales.date') }}</TableHead>
                        <TableHead>{{ t('sales::sales.cashier') }}</TableHead>
                        <TableHead>{{ t('sales::sales.payment') }}</TableHead>
                        <TableHead class="text-end">{{ t('sales::sales.total_column') }}</TableHead>
                        <TableHead>{{ t('sales::sales.status') }}</TableHead>
                        <TableHead class="text-end">{{ t('sales::sales.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="sale in sales.data" :key="sale.id">
                        <TableCell class="font-medium">{{ sale.invoice_number }}</TableCell>
                        <TableCell class="text-sm">{{ new Date(sale.created_at).toLocaleDateString() }}</TableCell>
                        <TableCell class="text-sm">{{ sale.user?.name ?? '—' }}</TableCell>
                        <TableCell class="text-sm">{{ paymentLabel(sale.payment_method) }}</TableCell>
                        <TableCell class="text-end font-mono tabular-nums">${{ Number(sale.total).toFixed(2) }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(sale.status)">{{ statusLabel(sale.status) }}</Badge>
                        </TableCell>
                        <TableCell class="text-end">
                            <Button variant="outline" size="sm">{{ t('sales::sales.view') }}</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <PaginationLinks
            v-if="props.sales.last_page > 1"
            :current-page="props.sales.current_page"
            :last-page="props.sales.last_page"
            :total="props.sales.total"
            :per-page="props.sales.per_page"
            :href-for="(page: number) => indexRoute.url({ query: { page } })"
        />
    </div>
</template>
