<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { index as indexRoute, store, edit } from '@/routes/inventory/products'

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
    }
    categories: Category[]
}>()

const form = useForm({
    name: '',
    barcode: '',
    price: '',
    cost: '0',
    stock_qty: '0',
    min_stock: '0',
    category_id: '',
    status: '1',
})

const createProduct = () => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}

const statusBadge = (status: number) => {
    switch (status) {
        case 1: return { variant: 'success' as const, label: 'Active' }
        case 2: return { variant: 'secondary' as const, label: 'Inactive' }
        case 3: return { variant: 'warning' as const, label: 'Out of Stock' }
        default: return { variant: 'outline' as const, label: 'Unknown' }
    }
}
</script>

<template>
    <Head title="Products" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Product Inventory</h1>
                <p class="text-sm text-muted-foreground">Manage your catalog, stock levels, and pricing.</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="'/inventory/products/import'">Import</Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="'/inventory/products/barcode/bulk'">Barcodes</Link>
                </Button>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button>Add Product</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Product</DialogTitle>
                            <DialogDescription>Add a new product to your inventory.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createProduct" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required />
                            </div>
                            <div class="grid gap-2">
                                <Label for="barcode">Barcode</Label>
                                <Input id="barcode" v-model="form.barcode" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="category_id">Category</Label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="">No Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label for="price">Price</Label>
                                <Input id="price" type="number" step="0.01" v-model="form.price" required />
                            </div>
                            <div class="grid gap-2">
                                <Label for="cost">Cost</Label>
                                <Input id="cost" type="number" step="0.01" v-model="form.cost" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="stock_qty">Stock Qty</Label>
                                <Input id="stock_qty" type="number" step="0.01" v-model="form.stock_qty" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="min_stock">Min Stock</Label>
                                <Input id="min_stock" type="number" step="0.01" v-model="form.min_stock" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">
                                    <Spinner v-if="form.processing" />
                                    Save
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Product Info</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Barcode</TableHead>
                        <TableHead>Stock Level</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in products.data" :key="product.id">
                        <TableCell>
                            <div>
                                <p class="font-medium">{{ product.name }}</p>
                                <p v-if="product.has_variants" class="text-xs text-muted-foreground">Has variants</p>
                            </div>
                        </TableCell>
                        <TableCell>{{ product.category?.name ?? '-' }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ product.barcode ?? '-' }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span :class="Number(product.stock_qty) <= Number(product.min_stock) ? 'text-destructive font-bold' : ''">
                                    {{ product.stock_qty }}
                                </span>
                                <Badge v-if="Number(product.stock_qty) <= Number(product.min_stock)" variant="warning">Low</Badge>
                            </div>
                        </TableCell>
                        <TableCell class="font-mono">{{ Number(product.price).toFixed(2) }} EGP</TableCell>
                        <TableCell>
                            <Badge :variant="statusBadge(product.status).variant">
                                {{ statusBadge(product.status).label }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="edit.url({ product: product.id })">Edit</Link>
                                </Button>
                                <Button v-if="product.has_variants" variant="outline" size="sm" as-child>
                                    <Link :href="`/inventory/products/${product.id}/variants`">Variants</Link>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-if="products.last_page > 1" class="flex justify-center gap-2">
            <Link
                v-for="page in products.last_page"
                :key="page"
                :href="indexRoute.url({ query: { page } })"
                class="rounded-md px-3 py-1 text-sm"
                :class="page === products.current_page ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
