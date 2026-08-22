<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { ref, computed } from 'vue'

interface Product {
    id: number
    name: string
    barcode: string | null
}

const props = defineProps<{
    products: Product[]
}>()

const selectedProducts = ref<number[]>([])
const quantity = ref(1)
const includePrice = ref(true)
const includeName = ref(true)
const searchQuery = ref('')

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products
    const q = searchQuery.value.toLowerCase()
    return props.products.filter(
        (p) => p.name.toLowerCase().includes(q) || (p.barcode && p.barcode.includes(q)),
    )
})

const toggleProduct = (id: number) => {
    const idx = selectedProducts.value.indexOf(id)
    if (idx === -1) {
        selectedProducts.value.push(id)
    } else {
        selectedProducts.value.splice(idx, 1)
    }
}

const toggleAll = () => {
    if (selectedProducts.value.length === filteredProducts.value.length) {
        selectedProducts.value = []
    } else {
        selectedProducts.value = filteredProducts.value.map((p) => p.id)
    }
}

const printSelected = () => {
    if (selectedProducts.value.length === 0) return
    const ids = selectedProducts.value.join(',')
    const params = new URLSearchParams({
        ids,
        qty: String(quantity.value),
        price: includePrice.value ? '1' : '0',
        name: includeName.value ? '1' : '0',
    })
    window.open(
        `/inventory/barcode/bulk?${params.toString()}`,
        '_blank',
        'width=800,height=600',
    )
}
</script>

<template>
    <Head title="Bulk Barcode Print" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Bulk Barcode Print</h1>
                <p class="text-sm text-muted-foreground">Generate barcode labels for multiple products at once.</p>
            </div>
            <Button @click="printSelected" :disabled="selectedProducts.length === 0">
                Print {{ selectedProducts.length }} Barcodes
            </Button>
        </div>

        <div class="rounded-md border p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="grid gap-2">
                <Label>Label Quantity Per Product</Label>
                <Input v-model.number="quantity" type="number" min="1" max="50" />
            </div>
            <label class="flex items-center gap-2">
                <input v-model="includePrice" type="checkbox" class="rounded border-input" />
                <span class="text-sm">Include Price</span>
            </label>
            <label class="flex items-center gap-2">
                <input v-model="includeName" type="checkbox" class="rounded border-input" />
                <span class="text-sm">Include Name</span>
            </label>
        </div>

        <div class="rounded-md border">
            <div class="px-4 py-3 border-b">
                <Input
                    v-model="searchQuery"
                    placeholder="Search products..."
                    class="max-w-sm"
                />
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12">
                            <input
                                type="checkbox"
                                :checked="selectedProducts.length === filteredProducts.length && filteredProducts.length > 0"
                                class="rounded border-input"
                                @change="toggleAll"
                            />
                        </TableHead>
                        <TableHead>Product</TableHead>
                        <TableHead>Barcode</TableHead>
                        <TableHead>Price</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in filteredProducts" :key="product.id">
                        <TableCell>
                            <input
                                type="checkbox"
                                :checked="selectedProducts.includes(product.id)"
                                class="rounded border-input"
                                @change="toggleProduct(product.id)"
                            />
                        </TableCell>
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ product.barcode ?? '-' }}</TableCell>
                        <TableCell class="font-mono">{{ product.price }}</TableCell>
                    </TableRow>
                    <TableRow v-if="filteredProducts.length === 0">
                        <TableCell colspan="4" class="text-center text-muted-foreground py-8">
                            No products found
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
