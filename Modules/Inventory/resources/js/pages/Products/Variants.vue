<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { PageHeader, ConfirmDialog } from '@/components/shared'

interface Product {
    id: number
    name: string
    barcode: string | null
    price: string
    cost: string
    stock_qty: string
    min_stock: string
    status: number
    has_variants: boolean
    category: { id: number; name: string } | null
}

interface Variant {
    id: number
    name: string
    sku: string
    barcode: string
    price: string
    stock_qty: number
    status: string
}

const props = defineProps<{
    product: Product
    variants: Variant[]
}>()

const { t } = useI18n()

const form = useForm({
    name: '',
    sku: '',
    barcode: '',
    price: '',
    stock_qty: '0',
})

const addVariant = () => {
    form.post(`/inventory/products/${props.product.id}/variants`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}

const variantToDelete = ref<Variant | null>(null)
const deleting = ref(false)

const deleteVariant = () => {
    if (!variantToDelete.value) return

    deleting.value = true
    router.delete(
        `/inventory/products/${props.product.id}/variants/${variantToDelete.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                variantToDelete.value = null
            },
            onFinish: () => {
                deleting.value = false
            },
        },
    )
}

const totalStock = props.variants.reduce((sum, v) => sum + v.stock_qty, 0)
const avgPrice = props.variants.length
    ? (props.variants.reduce((sum, v) => sum + parseFloat(v.price), 0) / props.variants.length).toFixed(2)
    : '0.00'
</script>

<template>
    <Head :title="t('inventory.variants.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('inventory.variants.title')" :description="t('inventory.variants.description')">
            <template #actions>
                <Button variant="outline" as-child>
                    <Link href="/inventory/products">{{ t('common.back') }}</Link>
                </Button>
                <Button as-child>
                    <a href="#add-variant">{{ t('inventory.variants.add') }}</a>
                </Button>
            </template>
        </PageHeader>

        <div class="rounded-md border">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <div>
                    <h2 class="font-semibold">{{ t('inventory.variants.sectionTitle') }}</h2>
                    <p class="text-sm text-muted-foreground">{{ t('inventory.variants.sectionDescription') }}</p>
                </div>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('inventory.variants.variantName') }}</TableHead>
                        <TableHead>{{ t('inventory.variants.sku') }}</TableHead>
                        <TableHead>{{ t('inventory.products.barcode') }}</TableHead>
                        <TableHead>{{ t('common.price') }} (EGP)</TableHead>
                        <TableHead>{{ t('inventory.variants.stock') }}</TableHead>
                        <TableHead class="w-16 text-right">{{ t('common.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="variants.length === 0">
                        <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                            {{ t('inventory.variants.empty') }}
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="variant in variants" :key="variant.id">
                        <TableCell class="font-medium">{{ variant.name }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ variant.sku }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ variant.barcode }}</TableCell>
                        <TableCell class="font-mono">{{ Number(variant.price).toFixed(2) }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span>{{ variant.stock_qty }}</span>
                                <Badge :variant="variant.stock_qty > 5 ? 'success' : 'warning'">
                                    {{ variant.stock_qty > 5 ? t('inventory.variants.inStock') : t('inventory.variants.lowStock') }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-destructive"
                                @click="variantToDelete = variant"
                            >
                                {{ t('common.delete') }}
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div id="add-variant" class="rounded-md border p-4 scroll-mt-24">
            <h3 class="font-semibold mb-4">{{ t('inventory.variants.addNew') }}</h3>
            <form @submit.prevent="addVariant" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="grid gap-2">
                    <Label for="variant-name">{{ t('common.name') }}</Label>
                    <Input
                        id="variant-name"
                        v-model="form.name"
                        :placeholder="t('inventory.variants.namePlaceholder')"
                        required
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="variant-sku">{{ t('inventory.variants.sku') }}</Label>
                    <Input
                        id="variant-sku"
                        v-model="form.sku"
                        class="font-mono"
                        :placeholder="t('inventory.variants.skuPlaceholder')"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="variant-barcode">{{ t('inventory.products.barcode') }}</Label>
                    <Input
                        id="variant-barcode"
                        v-model="form.barcode"
                        class="font-mono"
                        :placeholder="t('inventory.variants.barcodePlaceholder')"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="variant-price">{{ t('common.price') }} (EGP)</Label>
                    <Input
                        id="variant-price"
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="variant-stock">{{ t('inventory.products.stockQty') }}</Label>
                    <Input
                        id="variant-stock"
                        v-model="form.stock_qty"
                        type="number"
                        step="1"
                        min="0"
                        placeholder="0"
                    />
                </div>
                <div class="md:col-span-5 flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        {{ t('inventory.variants.add') }}
                    </Button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">{{ t('inventory.variants.totalVariations') }}</p>
                <p class="text-3xl font-bold">{{ variants.length }}</p>
            </div>
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">{{ t('inventory.variants.averagePrice') }}</p>
                <p class="text-3xl font-bold font-mono">{{ Number(avgPrice).toFixed(2) }} EGP</p>
            </div>
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">{{ t('inventory.variants.totalStock') }}</p>
                <p class="text-3xl font-bold font-mono">{{ totalStock }} {{ t('inventory.variants.units') }}</p>
            </div>
        </div>

        <ConfirmDialog
            :open="variantToDelete !== null"
            :processing="deleting"
            :description="t('inventory.variants.deleteConfirm')"
            @update:open="(open: boolean) => { if (!open) variantToDelete = null }"
            @confirm="deleteVariant"
        />
    </div>
</template>
