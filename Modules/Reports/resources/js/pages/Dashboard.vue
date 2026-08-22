<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head } from '@inertiajs/vue3';
import {
    Banknote,
    Download,
    Gauge,
    ReceiptText,
    TrendingDown,
    TrendingUp,
    Zap,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface TopProduct {
    product_id: number | null;
    name: string;
    total_qty: string;
    total_revenue: string;
}

interface LowStockProduct {
    id: number;
    name: string;
    category_id: number | null;
    category: { id: number; name: string } | null;
    stock_qty: string;
    min_stock: string;
}

interface SalesTrendPoint {
    date: string;
    total: number;
}

interface PaymentBreakdown {
    payment_method: string;
    count: number;
    total: number;
    percentage: number;
}

const { t, te } = useI18n();

const props = defineProps<{
    metrics: {
        today_sales: number;
        yesterday_sales: number;
        yesterday_transactions: number;
        yesterday_avg_sale: number;
        transaction_count: number;
        avg_sale: number;
        top_product: TopProduct | null;
        low_stock_count: number;
        low_stock_products: LowStockProduct[];
        sales_trend: SalesTrendPoint[];
        payment_breakdown: PaymentBreakdown[];
    };
}>();

const trend = (current: number, previous: number) => {
    if (previous === 0) {
return { value: '—', up: true };
}

    const pct = ((current - previous) / previous) * 100;

    return {
        value: `${pct >= 0 ? '+' : ''}${pct.toFixed(1)}%`,
        up: pct >= 0,
    };
};

const maxTrend = Math.max(...props.metrics.sales_trend.map((d) => d.total), 1);

const circumference = 2 * Math.PI * 80;
const paymentColors = ['bg-primary', 'bg-secondary', 'bg-amber-600', 'bg-blue-500'];

const donutSegments = () => {
    let offset = 0;

    return props.metrics.payment_breakdown.map((pm) => {
        const dashLength = (pm.percentage / 100) * circumference;
        const seg = {
            ...pm,
            dashLength,
            offset: -offset,
        };
        offset += dashLength;

        return seg;
    });
};

const methodKeys: Record<string, string> = {
    cash: 'payment_cash',
    card: 'payment_card',
    mixed: 'payment_mixed',
    deferred: 'payment_deferred',
};

const paymentLabel = (method: string): string => {
    const key = `reports::dashboard.${methodKeys[method]}`;

    return te(key) ? t(key) : method;
};

const priorityLevel = (stock: number, min: number) => {
    const ratio = stock / min;

    if (ratio <= 0.3) {
return { label: t('reports::dashboard.critical'), class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' };
}

    return { label: t('reports::dashboard.warning'), class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' };
};
</script>

<template>
    <Head :title="t('reports::dashboard.title')" />

    <div class="mx-auto flex max-w-[1600px] flex-col gap-6 p-6">
        <PageHeader :title="t('reports::dashboard.heading')" />

        <!-- KPI Cards -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative flex h-32 flex-col justify-between overflow-hidden rounded-xl border bg-card p-5">
                <div class="absolute -bottom-4 -end-4 opacity-10 transition-transform group-hover:scale-110">
                    <Banknote class="size-20" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.total_sales_today') }}</p>
                    <p class="mt-1 font-mono text-3xl font-bold text-primary tabular-nums">${{ Number(metrics.today_sales).toFixed(2) }}</p>
                </div>
                <div class="flex items-center gap-1 text-xs font-bold" :class="trend(metrics.today_sales, metrics.yesterday_sales).up ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive'">
                    <component :is="trend(metrics.today_sales, metrics.yesterday_sales).up ? TrendingUp : TrendingDown" class="size-3.5" />
                    {{ trend(metrics.today_sales, metrics.yesterday_sales).value }} {{ t('reports::dashboard.vs_yesterday') }}
                </div>
            </div>
            <div class="group relative flex h-32 flex-col justify-between overflow-hidden rounded-xl border bg-card p-5">
                <div class="absolute -bottom-4 -end-4 opacity-10 transition-transform group-hover:scale-110">
                    <ReceiptText class="size-20" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.transactions') }}</p>
                    <p class="mt-1 text-3xl font-bold text-primary tabular-nums">{{ metrics.transaction_count }}</p>
                </div>
                <div class="flex items-center gap-1 text-xs font-bold" :class="trend(metrics.transaction_count, metrics.yesterday_transactions).up ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive'">
                    <component :is="trend(metrics.transaction_count, metrics.yesterday_transactions).up ? TrendingUp : TrendingDown" class="size-3.5" />
                    {{ trend(metrics.transaction_count, metrics.yesterday_transactions).value }} {{ t('reports::dashboard.vs_yesterday') }}
                </div>
            </div>
            <div class="group relative flex h-32 flex-col justify-between overflow-hidden rounded-xl border bg-card p-5">
                <div class="absolute -bottom-4 -end-4 opacity-10 transition-transform group-hover:scale-110">
                    <Gauge class="size-20" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.average_sale') }}</p>
                    <p class="mt-1 font-mono text-3xl font-bold text-primary tabular-nums">${{ Number(metrics.avg_sale).toFixed(2) }}</p>
                </div>
                <div class="flex items-center gap-1 text-xs font-bold" :class="trend(metrics.avg_sale, metrics.yesterday_avg_sale).up ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive'">
                    <component :is="trend(metrics.avg_sale, metrics.yesterday_avg_sale).up ? TrendingUp : TrendingDown" class="size-3.5" />
                    {{ trend(metrics.avg_sale, metrics.yesterday_avg_sale).value }} {{ t('reports::dashboard.vs_yesterday') }}
                </div>
            </div>
            <div class="flex flex-col items-center justify-center rounded-xl bg-primary p-5 text-center text-primary-foreground">
                <Zap class="mb-1 size-7 text-amber-400" />
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">{{ t('reports::dashboard.quick_action') }}</p>
                <p class="mt-2 text-base font-semibold">{{ t('reports::dashboard.run_eod') }}</p>
            </div>
        </section>

        <!-- Charts Row -->
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- 30-Day Sales Trend -->
            <div class="rounded-xl border bg-card p-5 lg:col-span-2">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">{{ t('reports::dashboard.sales_performance') }}</h3>
                        <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.revenue_trend') }}</p>
                    </div>
                </div>
                <div class="flex h-56 w-full items-end gap-1 px-2">
                    <div
                        v-for="(point, idx) in metrics.sales_trend"
                        :key="point.date"
                        class="flex-1 rounded-t-sm transition-all duration-200 hover:opacity-80"
                        :class="idx === metrics.sales_trend.length - 1 ? 'bg-primary' : 'bg-primary/40'"
                        :style="{ height: `${Math.max((point.total / maxTrend) * 100, 2)}%` }"
                        :title="`${point.date}: $${Number(point.total).toFixed(2)}`"
                    />
                </div>
                <div class="mt-2 flex justify-between px-2 text-xs text-muted-foreground">
                    <span>{{ metrics.sales_trend[0]?.date }}</span>
                    <span>{{ metrics.sales_trend[Math.floor(metrics.sales_trend.length / 2)]?.date }}</span>
                    <span>{{ metrics.sales_trend[metrics.sales_trend.length - 1]?.date }}</span>
                </div>
            </div>

            <!-- Payment Breakdown Donut -->
            <div class="flex flex-col rounded-xl border bg-card p-5">
                <h3 class="mb-4 text-lg font-semibold">{{ t('reports::dashboard.payment_methods') }}</h3>
                <div class="relative flex flex-1 flex-col items-center justify-center">
                    <svg class="h-48 w-48 -rotate-90" viewBox="0 0 192 192">
                        <circle cx="96" cy="96" fill="transparent" r="80" stroke="#f1f5f9" stroke-width="20" />
                        <circle
                            v-for="seg in donutSegments()"
                            :key="seg.payment_method"
                            cx="96"
                            cy="96"
                            fill="transparent"
                            r="80"
                            :stroke="['#070235', '#006c4a', '#cf7100', '#3b82f6'][metrics.payment_breakdown.indexOf(seg)]"
                            :stroke-dasharray="`${seg.dashLength} ${circumference}`"
                            :stroke-dashoffset="seg.offset"
                            stroke-width="20"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold tabular-nums">{{ metrics.payment_breakdown.length }}</span>
                        <span class="text-[10px] font-bold uppercase text-muted-foreground">{{ t('reports::dashboard.methods') }}</span>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div
                        v-for="(pm, idx) in metrics.payment_breakdown"
                        :key="pm.payment_method"
                        class="flex items-center gap-2"
                    >
                        <div
                            class="size-3 shrink-0 rounded-full"
                            :class="paymentColors[idx]"
                        />
                        <span class="text-sm tabular-nums">{{ paymentLabel(pm.payment_method) }} ({{ pm.percentage }}%)</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Low Stock Table -->
        <section class="overflow-hidden rounded-xl border bg-card">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <div>
                    <h3 class="text-lg font-semibold">{{ t('reports::dashboard.low_stock_alerts') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.items_requiring_reorder') }}</p>
                </div>
                <Button variant="outline" size="sm">
                    <Download class="size-3.5" />
                    {{ t('reports::dashboard.export_list') }}
                </Button>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('reports::dashboard.product_name') }}</TableHead>
                        <TableHead>{{ t('reports::dashboard.category') }}</TableHead>
                        <TableHead class="text-end">{{ t('reports::dashboard.current_stock') }}</TableHead>
                        <TableHead class="text-end">{{ t('reports::dashboard.min_stock') }}</TableHead>
                        <TableHead class="text-center">{{ t('reports::dashboard.priority') }}</TableHead>
                        <TableHead class="text-end">{{ t('reports::dashboard.action') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="metrics.low_stock_products.length === 0">
                        <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                            {{ t('reports::dashboard.no_low_stock') }}
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="product in metrics.low_stock_products" :key="product.id">
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell class="text-sm text-muted-foreground">{{ product.category?.name ?? '—' }}</TableCell>
                        <TableCell class="font-mono text-sm tabular-nums" :class="{ 'text-destructive': Number(product.stock_qty) <= Number(product.min_stock) * 0.3 }">
                            {{ t('reports::dashboard.units', { n: Number(product.stock_qty).toFixed(0) }) }}
                        </TableCell>
                        <TableCell class="text-end font-mono text-sm tabular-nums">{{ t('reports::dashboard.units', { n: Number(product.min_stock).toFixed(0) }) }}</TableCell>
                        <TableCell class="text-center">
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-bold uppercase"
                                :class="priorityLevel(Number(product.stock_qty), Number(product.min_stock)).class"
                            >
                                {{ priorityLevel(Number(product.stock_qty), Number(product.min_stock)).label }}
                            </span>
                        </TableCell>
                        <TableCell class="text-end">
                            <Button variant="link" size="sm">{{ t('reports::dashboard.reorder') }}</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </section>

        <!-- Top Product Card -->
        <section v-if="metrics.top_product" class="rounded-xl border bg-card p-5">
            <h3 class="mb-3 text-lg font-semibold">{{ t('reports::dashboard.top_product') }}</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.name') }}</p>
                    <p class="font-medium">{{ metrics.top_product.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.qty_sold') }}</p>
                    <p class="font-medium tabular-nums">{{ t('reports::dashboard.units', { n: Number(metrics.top_product.total_qty).toFixed(0) }) }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ t('reports::dashboard.revenue') }}</p>
                    <p class="font-mono font-medium tabular-nums">${{ Number(metrics.top_product.total_revenue).toFixed(2) }}</p>
                </div>
            </div>
        </section>
    </div>
</template>
