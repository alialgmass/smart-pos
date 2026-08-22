<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

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

const deleteVariant = (variant: Variant) => {
    if (confirm('Delete this variant?')) {
        useForm({}).delete(
            `/inventory/products/${props.product.id}/variants/${variant.id}`,
            { preserveScroll: true },
        )
    }
}

const totalStock = props.variants.reduce((sum, v) => sum + v.stock_qty, 0)
const avgPrice = props.variants.length
    ? (props.variants.reduce((sum, v) => sum + parseFloat(v.price), 0) / props.variants.length).toFixed(2)
    : '0.00'
</script>

<template>
    <Head title="Product Variants" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                    <Link href="/inventory/products" class="hover:text-primary">Products</Link>
                    <span>/</span>
                    <span class="text-foreground font-medium">{{ product.name }}</span>
                </nav>
                <h1 class="text-2xl font-bold">Product Variants</h1>
                <p class="text-sm text-muted-foreground">Manage product variations like size, color, or material.</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="'/inventory/products'">Back</Link>
                </Button>
                <Button @click="addVariant" :disabled="form.processing">Add Variant</Button>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <div>
                    <h2 class="font-semibold">Product Variants</h2>
                    <p class="text-sm text-muted-foreground">Add variations like size, color, or material.</p>
                </div>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Variant Name</TableHead>
                        <TableHead>SKU</TableHead>
                        <TableHead>Barcode</TableHead>
                        <TableHead>Price (EGP)</TableHead>
                        <TableHead>Stock</TableHead>
                        <TableHead class="w-16">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="variants.length === 0">
                        <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                            No variants yet. Add one below.
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
                                    {{ variant.stock_qty > 5 ? 'In Stock' : 'Low Stock' }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Button variant="ghost" size="sm" class="text-destructive" @click="deleteVariant(variant)">
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="rounded-md border p-4">
            <h3 class="font-semibold mb-4">Add New Variant</h3>
            <form @submit.prevent="addVariant" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input
                        v-model="form.name"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="e.g. Small / Navy Blue"
                        required
                    />
                </div>
                <div>
                    <label class="text-sm font-medium">SKU</label>
                    <input
                        v-model="form.sku"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono"
                        placeholder="e.g. TSH-NAV-S"
                    />
                </div>
                <div>
                    <label class="text-sm font-medium">Barcode</label>
                    <input
                        v-model="form.barcode"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono"
                        placeholder="e.g. 6221234567890"
                    />
                </div>
                <div>
                    <label class="text-sm font-medium">Price (EGP)</label>
                    <input
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="0.00"
                        required
                    />
                </div>
                <div>
                    <label class="text-sm font-medium">Stock Qty</label>
                    <input
                        v-model="form.stock_qty"
                        type="number"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="0"
                    />
                </div>
                <div class="md:col-span-5 flex justify-end">
                    <Button type="submit" :disabled="form.processing">Add Variant</Button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">Total Variations</p>
                <p class="text-3xl font-bold">{{ variants.length }}</p>
            </div>
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">Average Price</p>
                <p class="text-3xl font-bold font-mono">{{ Number(avgPrice).toFixed(2) }} EGP</p>
            </div>
            <div class="rounded-md border p-4 bg-muted/30">
                <p class="text-sm text-muted-foreground">Total Stock</p>
                <p class="text-3xl font-bold font-mono">{{ totalStock }} Units</p>
            </div>
        </div>
    </div>
</template>
