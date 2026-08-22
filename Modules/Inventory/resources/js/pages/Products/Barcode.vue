<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ref } from 'vue'

const props = defineProps<{
    product?: {
        id: number
        name: string
        barcode: string | null
    }
}>()

const barcodeValue = ref(props.product?.barcode ?? '')
const quantity = ref(1)
const includePrice = ref(true)
const includeName = ref(true)

const barcodeUrl = computed(() => {
    if (!barcodeValue.value) return null
    const params = new URLSearchParams({
        code: barcodeValue.value,
        qty: String(quantity.value),
        price: includePrice.value ? '1' : '0',
        name: includeName.value ? '1' : '0',
    })
    return `/inventory/barcode/generate?${params.toString()}`
})

import { computed } from 'vue'

const printBarcodes = () => {
    if (!barcodeUrl.value) return
    window.open(barcodeUrl.value, '_blank', 'width=800,height=600')
}
</script>

<template>
    <Head title="Generate Barcode" />

    <div class="flex flex-col gap-6 p-6 max-w-2xl">
        <div>
            <h1 class="text-2xl font-bold">Generate Barcode</h1>
            <p class="text-sm text-muted-foreground">Create and print barcode labels for your products.</p>
        </div>

        <div v-if="product" class="rounded-md border p-4 bg-muted/30">
            <p class="text-sm font-medium">Product: {{ product.name }}</p>
            <p class="text-sm text-muted-foreground font-mono" v-if="product.barcode">Current barcode: {{ product.barcode }}</p>
        </div>

        <div class="rounded-md border p-6 space-y-4">
            <div class="grid gap-2">
                <Label for="barcode">Barcode Value</Label>
                <Input id="barcode" v-model="barcodeValue" placeholder="Enter barcode number" class="font-mono" />
            </div>

            <div class="grid gap-2">
                <Label for="quantity">Quantity</Label>
                <Input id="quantity" v-model.number="quantity" type="number" min="1" max="100" />
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2">
                    <input v-model="includePrice" type="checkbox" class="rounded border-input" />
                    <span class="text-sm">Include Price</span>
                </label>
                <label class="flex items-center gap-2">
                    <input v-model="includeName" type="checkbox" class="rounded border-input" />
                    <span class="text-sm">Include Name</span>
                </label>
            </div>

            <div class="flex gap-2">
                <Button @click="printBarcodes" :disabled="!barcodeValue">Generate & Print</Button>
                <Button variant="outline" @click="barcodeUrl ? window.open(barcodeUrl, '_blank') : undefined" :disabled="!barcodeUrl">
                    View PDF
                </Button>
            </div>
        </div>

        <div v-if="barcodeUrl" class="rounded-md border p-6 flex justify-center">
            <img :src="barcodeUrl" alt="Barcode preview" class="max-w-full" />
        </div>
    </div>
</template>
