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
import { topProducts } from '@/routes/reports';
import { Head, useForm } from '@inertiajs/vue3';
import { PackageOpen } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface Product {
    product_id: number | null;
    name: string;
    total_qty: string;
    total_revenue: string;
}

const { t } = useI18n();

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
    form.get(topProducts.url(), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="t('reports::top_products.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('reports::top_products.title')" />

        <form @submit.prevent="search" class="flex flex-wrap items-end gap-4">
            <div class="grid gap-2">
                <Label for="limit">{{ t('reports::top_products.limit') }}</Label>
                <Input id="limit" v-model="form.limit" type="number" class="w-24" />
            </div>
            <div class="grid gap-2">
                <Label for="start_date">{{ t('reports::top_products.start_date') }}</Label>
                <Input id="start_date" v-model="form.start_date" type="date" />
            </div>
            <div class="grid gap-2">
                <Label for="end_date">{{ t('reports::top_products.end_date') }}</Label>
                <Input id="end_date" v-model="form.end_date" type="date" />
            </div>
            <Button type="submit" :disabled="form.processing">{{ t('reports::top_products.filter') }}</Button>
        </form>

        <EmptyState
            v-if="products.length === 0"
            :icon="PackageOpen"
            :title="t('reports::top_products.no_data')"
        />

        <div v-else class="overflow-hidden rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>{{ t('reports::top_products.product') }}</TableHead>
                        <TableHead>{{ t('reports::top_products.qty_sold') }}</TableHead>
                        <TableHead>{{ t('reports::top_products.revenue') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(product, index) in products" :key="product.product_id ?? index">
                        <TableCell class="text-muted-foreground">{{ index + 1 }}</TableCell>
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell class="tabular-nums">{{ Number(product.total_qty).toFixed(0) }}</TableCell>
                        <TableCell class="font-mono tabular-nums">${{ Number(product.total_revenue).toFixed(2) }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
