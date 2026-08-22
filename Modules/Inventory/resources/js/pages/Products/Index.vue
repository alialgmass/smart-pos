<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import type { AcceptableValue } from 'reka-ui'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { PageHeader, EmptyState, SearchFilterBar, PaginationLinks, ConfirmDialog } from '@/components/shared'
import { index as indexRoute, store, edit as editRoute, destroy } from '@/routes/inventory/products'
import { applyFilters, currentFilter } from '@/composables/usePageFilters'

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

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    products: {
        data: Product[]
        current_page: number
        last_page: number
        total: number
        per_page: number
    }
    categories: Category[]
}>()

const { t } = useI18n()

const form = useForm({
    name: '',
    barcode: '',
    price: '',
    cost: '0',
    stock_qty: '0',
    min_stock: '0',
    category_id: 'none',
    status: '1',
}).transform((data) => ({
    ...data,
    category_id: data.category_id === 'none' ? null : data.category_id,
}))

const search = ref(currentFilter('search'))
const categoryId = ref(currentFilter('category_id') || 'all')
const statusId = ref(currentFilter('status') || 'all')

const onSearch = (value: string) => applyFilters('/inventory/products', { search: value })

const onCategoryChange = (value: AcceptableValue) => {
    categoryId.value = String(value)
    applyFilters('/inventory/products', { category_id: value === 'all' ? null : String(value) })
}

const onStatusChange = (value: AcceptableValue) => {
    statusId.value = String(value)
    applyFilters('/inventory/products', { status: value === 'all' ? null : String(value) })
}

const resetFilters = () => {
    search.value = ''
    categoryId.value = 'all'
    statusId.value = 'all'
    applyFilters('/inventory/products', { search: '', category_id: '', status: '' })
}

const createProduct = () => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}

const statusOptions = [
    { value: '1', key: 'status.active' },
    { value: '2', key: 'status.inactive' },
    { value: '3', key: 'status.outOfStock' },
]

const statusBadge = (status: number) => {
    switch (status) {
        case 1: return { variant: 'success' as const, key: 'status.active' }
        case 2: return { variant: 'secondary' as const, key: 'status.inactive' }
        case 3: return { variant: 'warning' as const, key: 'status.outOfStock' }
        default: return { variant: 'outline' as const, key: 'common.unknown' }
    }
}

const productToDelete = ref<Product | null>(null)
const deleting = ref(false)

const isFiltered = computed(
    () => Boolean(search.value) || categoryId.value !== 'all' || statusId.value !== 'all',
)

const confirmDelete = () => {
    if (!productToDelete.value) return

    deleting.value = true
    router.delete(destroy.url({ product: productToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            productToDelete.value = null
        },
        onFinish: () => {
            deleting.value = false
        },
    })
}

const pageHref = (page: number): string => {
    const params = new URLSearchParams(window.location.search)
    params.set('page', String(page))
    return `${indexRoute.url()}?${params.toString()}`
}
</script>

<template>
    <Head :title="t('inventory.products.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            :title="t('inventory.products.heading')"
            :description="t('inventory.products.description')"
        >
            <template #actions>
                <Button variant="outline" as-child>
                    <Link href="/inventory/products/import">{{ t('common.import') }}</Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link href="/inventory/products/barcode/bulk">{{ t('inventory.barcode.bulkTitle') }}</Link>
                </Button>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button>{{ t('inventory.products.add') }}</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>{{ t('inventory.products.createTitle') }}</DialogTitle>
                            <DialogDescription>{{ t('inventory.products.createDescription') }}</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createProduct" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">{{ t('common.name') }}</Label>
                                <Input id="name" v-model="form.name" required />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="barcode">{{ t('inventory.products.barcode') }}</Label>
                                    <Input id="barcode" v-model="form.barcode" class="font-mono" />
                                </div>
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
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="price">{{ t('common.price') }}</Label>
                                    <Input id="price" type="number" step="0.01" min="0" v-model="form.price" required />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="cost">{{ t('common.cost') }}</Label>
                                    <Input id="cost" type="number" step="0.01" min="0" v-model="form.cost" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="stock_qty">{{ t('inventory.products.stockQty') }}</Label>
                                    <Input id="stock_qty" type="number" step="1" min="0" v-model="form.stock_qty" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="min_stock">{{ t('inventory.products.minStock') }}</Label>
                                    <Input id="min_stock" type="number" step="1" min="0" v-model="form.min_stock" />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">
                                    <Spinner v-if="form.processing" />
                                    {{ t('common.save') }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </template>
        </PageHeader>

        <SearchFilterBar v-model:search="search" @update:search="onSearch" @reset="resetFilters">
            <Select :model-value="categoryId" @update:model-value="onCategoryChange">
                <SelectTrigger class="w-44">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{ t('inventory.products.allCategories') }}</SelectItem>
                    <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                        {{ cat.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select :model-value="statusId" @update:model-value="onStatusChange">
                <SelectTrigger class="w-40">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">{{ t('inventory.products.allStatuses') }}</SelectItem>
                    <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                        {{ t(opt.key) }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </SearchFilterBar>

        <EmptyState
            v-if="products.data.length === 0 && !isFiltered"
            :title="t('emptyState.title')"
            :description="t('emptyState.description')"
        />

        <EmptyState
            v-else-if="products.data.length === 0"
            :title="t('emptyState.noSearchResults')"
        />

        <div v-else class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('inventory.products.info') }}</TableHead>
                        <TableHead>{{ t('inventory.products.category') }}</TableHead>
                        <TableHead>{{ t('inventory.products.barcode') }}</TableHead>
                        <TableHead>{{ t('inventory.products.stockLevel') }}</TableHead>
                        <TableHead>{{ t('common.price') }}</TableHead>
                        <TableHead>{{ t('common.status') }}</TableHead>
                        <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in products.data" :key="product.id">
                        <TableCell>
                            <div>
                                <p class="font-medium">{{ product.name }}</p>
                                <p v-if="product.has_variants" class="text-xs text-muted-foreground">
                                    {{ t('inventory.products.hasVariants') }}
                                </p>
                            </div>
                        </TableCell>
                        <TableCell>{{ product.category?.name ?? '-' }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ product.barcode ?? '-' }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span :class="Number(product.stock_qty) <= Number(product.min_stock) ? 'text-destructive font-bold' : ''">
                                    {{ product.stock_qty }}
                                </span>
                                <Badge v-if="Number(product.stock_qty) <= Number(product.min_stock)" variant="warning">
                                    {{ t('status.low') }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell class="font-mono">{{ Number(product.price).toFixed(2) }} EGP</TableCell>
                        <TableCell>
                            <Badge :variant="statusBadge(product.status).variant">
                                {{ t(statusBadge(product.status).key) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="editRoute.url({ product: product.id })">{{ t('common.edit') }}</Link>
                                </Button>
                                <Button v-if="product.has_variants" variant="outline" size="sm" as-child>
                                    <Link :href="`/inventory/products/${product.id}/variants`">{{ t('inventory.products.variants') }}</Link>
                                </Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="productToDelete = product">
                                    {{ t('common.delete') }}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <PaginationLinks
            v-if="products.last_page > 1"
            :current-page="products.current_page"
            :last-page="products.last_page"
            :total="products.total"
            :per-page="products.per_page"
            :href-for="pageHref"
        />

        <ConfirmDialog
            :open="productToDelete !== null"
            :processing="deleting"
            @update:open="(open: boolean) => { if (!open) productToDelete = null }"
            @confirm="confirmDelete"
        />
    </div>
</template>
