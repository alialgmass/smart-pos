<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { ref, computed } from 'vue'
import { PackageOpen } from 'lucide-vue-next'
import { PageHeader, EmptyState, SearchFilterBar } from '@/components/shared'

interface Product {
    id: number
    name: string
    barcode: string | null
    price: string
}

const props = defineProps<{
    products: Product[]
}>()

const { t } = useI18n()

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

const allSelected = computed(
    () =>
        filteredProducts.value.length > 0 &&
        selectedProducts.value.length === filteredProducts.value.length,
)

const toggleProduct = (id: number) => {
    const idx = selectedProducts.value.indexOf(id)
    if (idx === -1) {
        selectedProducts.value.push(id)
    } else {
        selectedProducts.value.splice(idx, 1)
    }
}

const toggleAll = (checked: boolean) => {
    selectedProducts.value = checked ? filteredProducts.value.map((p) => p.id) : []
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
    <Head :title="t('inventory.barcode.bulkTitle')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('inventory.barcode.bulkTitle')" :description="t('inventory.barcode.bulkDescription')">
            <template #actions>
                <Button @click="printSelected" :disabled="selectedProducts.length === 0">
                    {{ t('inventory.barcode.printCount', { count: selectedProducts.length }) }}
                </Button>
            </template>
        </PageHeader>

        <div class="rounded-md border p-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <div class="grid gap-2">
                <Label for="quantity">{{ t('inventory.barcode.labelQuantity') }}</Label>
                <Input id="quantity" v-model.number="quantity" type="number" min="1" max="50" />
            </div>
            <label class="flex items-center gap-2">
                <Checkbox :checked="includePrice" @update:checked="includePrice = $event" />
                <span class="text-sm">{{ t('inventory.barcode.includePrice') }}</span>
            </label>
            <label class="flex items-center gap-2">
                <Checkbox :checked="includeName" @update:checked="includeName = $event" />
                <span class="text-sm">{{ t('inventory.barcode.includeName') }}</span>
            </label>
        </div>

        <SearchFilterBar
            :placeholder="t('inventory.barcode.searchProducts')"
            @update:search="searchQuery = $event"
            @reset="searchQuery = ''"
        />

        <EmptyState
            v-if="filteredProducts.length === 0"
            :icon="PackageOpen"
            :title="t('inventory.barcode.noProducts')"
        />

        <div v-else class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12">
                            <Checkbox :checked="allSelected" @update:checked="toggleAll" />
                        </TableHead>
                        <TableHead>{{ t('inventory.barcode.product') }}</TableHead>
                        <TableHead>{{ t('inventory.products.barcode') }}</TableHead>
                        <TableHead>{{ t('common.price') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="cursor-pointer"
                        @click="toggleProduct(product.id)"
                    >
                        <TableCell @click.stop>
                            <Checkbox
                                :checked="selectedProducts.includes(product.id)"
                                @update:checked="() => toggleProduct(product.id)"
                            />
                        </TableCell>
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ product.barcode ?? '-' }}</TableCell>
                        <TableCell class="font-mono">{{ Number(product.price).toFixed(2) }} EGP</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
