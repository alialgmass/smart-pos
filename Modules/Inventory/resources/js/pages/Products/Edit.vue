<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { index as indexRoute, update } from '@/routes/inventory/products'

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    product: {
        id: number
        name: string
        barcode: string | null
        price: string
        cost: string
        stock_qty: string
        min_stock: string
        status: number
        has_variants: boolean
        category_id: number | null
        category: { id: number; name: string } | null
    }
    categories: Category[]
}>()

const { t } = useI18n()

const form = useForm({
    name: props.product.name,
    barcode: props.product.barcode ?? '',
    price: props.product.price,
    cost: props.product.cost,
    stock_qty: props.product.stock_qty,
    min_stock: props.product.min_stock,
    category_id: props.product.category_id ? String(props.product.category_id) : 'none',
    status: String(props.product.status),
}).transform((data) => ({
    ...data,
    category_id: data.category_id === 'none' ? null : data.category_id,
}))

const updateProduct = () => {
    form.put(update.url({ product: props.product.id }), {
        preserveScroll: true,
    })
}

const statusOptions = [
    { value: '1', key: 'status.active' },
    { value: '2', key: 'status.inactive' },
    { value: '3', key: 'status.outOfStock' },
]
</script>

<template>
    <Head :title="t('inventory.products.editTitle', { name: product.name })" />

    <div class="flex flex-col gap-6 p-6 max-w-3xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <nav class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                    <Link href="/inventory/products" class="hover:text-primary">{{ t('inventory.products.title') }}</Link>
                    <span>/</span>
                    <span class="text-foreground font-medium truncate">{{ product.name }}</span>
                </nav>
                <h1 class="truncate text-2xl font-bold">{{ product.name }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('inventory.products.editDescription') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="`/inventory/products/${product.id}/variants`">
                        {{ t('inventory.products.manageVariants') }}
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="indexRoute.url()">{{ t('common.back') }}</Link>
                </Button>
            </div>
        </div>

        <form @submit.prevent="updateProduct" class="space-y-6">
            <div class="rounded-md border p-6 space-y-4">
                <h2 class="font-semibold">{{ t('inventory.products.basicInfo') }}</h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">{{ t('inventory.products.productName') }}</Label>
                        <Input id="name" v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="barcode">{{ t('inventory.products.barcode') }}</Label>
                        <Input id="barcode" v-model="form.barcode" class="font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="category_id">{{ t('inventory.products.category') }}</Label>
                        <Select v-model="form.category_id">
                            <SelectTrigger id="category_id" class="w-full">
                                <SelectValue :placeholder="t('inventory.products.noCategory')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">{{ t('inventory.products.noCategory') }}</SelectItem>
                                <SelectItem
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="String(cat.id)"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">{{ t('common.status') }}</Label>
                        <Select v-model="form.status">
                            <SelectTrigger id="status" class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ t(opt.key) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <div class="rounded-md border p-6 space-y-4">
                <h2 class="font-semibold">{{ t('inventory.products.pricingInventory') }}</h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="price">{{ t('common.price') }} (EGP)</Label>
                        <Input id="price" v-model="form.price" type="number" step="0.01" min="0" required class="font-mono" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cost">{{ t('common.cost') }} (EGP)</Label>
                        <Input id="cost" v-model="form.cost" type="number" step="0.01" min="0" class="font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="stock_qty">{{ t('inventory.products.stockQuantity') }}</Label>
                        <Input id="stock_qty" v-model="form.stock_qty" type="number" step="1" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="min_stock">{{ t('inventory.products.minStockLevel') }}</Label>
                        <Input id="min_stock" v-model="form.min_stock" type="number" step="1" min="0" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button variant="outline" as-child>
                    <Link :href="indexRoute.url()">{{ t('common.discard') }}</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    {{ t('common.saveChanges') }}
                </Button>
            </div>
        </form>
    </div>
</template>
