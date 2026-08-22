<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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

const form = useForm({
    name: props.product.name,
    barcode: props.product.barcode ?? '',
    price: props.product.price,
    cost: props.product.cost,
    stock_qty: props.product.stock_qty,
    min_stock: props.product.min_stock,
    category_id: String(props.product.category_id ?? ''),
    status: String(props.product.status),
})

const updateProduct = () => {
    form.put(update.url({ product: props.product.id }), {
        preserveScroll: true,
    })
}

const statusOptions = [
    { value: '1', label: 'Active' },
    { value: '2', label: 'Inactive' },
    { value: '3', label: 'Out of Stock' },
]
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <div class="flex flex-col gap-6 p-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                    <Link href="/inventory/products" class="hover:text-primary">Products</Link>
                    <span>/</span>
                    <span class="text-foreground font-medium">{{ product.name }}</span>
                </nav>
                <h1 class="text-2xl font-bold">{{ product.name }}</h1>
                <p class="text-sm text-muted-foreground">Manage product details, inventory, and pricing.</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="`/inventory/products/${product.id}/variants`">
                        Manage Variants
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="indexRoute.url()">Back</Link>
                </Button>
            </div>
        </div>

        <form @submit.prevent="updateProduct" class="space-y-6">
            <div class="rounded-md border p-6 space-y-4">
                <h2 class="font-semibold">Basic Information</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Product Name</Label>
                        <Input id="name" v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="barcode">Barcode</Label>
                        <Input id="barcode" v-model="form.barcode" class="font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="category_id">Category</Label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">No Category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-md border p-6 space-y-4">
                <h2 class="font-semibold">Pricing & Inventory</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="price">Price (EGP)</Label>
                        <Input id="price" v-model="form.price" type="number" step="0.01" required class="font-mono" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cost">Cost (EGP)</Label>
                        <Input id="cost" v-model="form.cost" type="number" step="0.01" class="font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="stock_qty">Stock Quantity</Label>
                        <Input id="stock_qty" v-model="form.stock_qty" type="number" step="1" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="min_stock">Min Stock Level</Label>
                        <Input id="min_stock" v-model="form.min_stock" type="number" step="1" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button variant="outline" as-child>
                    <Link :href="indexRoute.url()">Discard</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>
