<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { ref, computed } from 'vue'
import { PageHeader } from '@/components/shared'

const props = defineProps<{
    product?: {
        id: number
        name: string
        barcode: string | null
    }
}>()

const { t } = useI18n()

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

const printBarcodes = () => {
    if (!barcodeUrl.value) return
    window.open(barcodeUrl.value, '_blank', 'width=800,height=600')
}
</script>

<template>
    <Head :title="t('inventory.barcode.generateTitle')" />

    <div class="flex flex-col gap-6 p-6 max-w-2xl">
        <PageHeader :title="t('inventory.barcode.generateTitle')" :description="t('inventory.barcode.generateDescription')">
            <template #actions>
                <Button variant="outline" as-child>
                    <Link href="/inventory/products">{{ t('common.back') }}</Link>
                </Button>
            </template>
        </PageHeader>

        <div v-if="product" class="rounded-md border p-4 bg-muted/30 space-y-1">
            <p class="text-sm font-medium">{{ t('inventory.barcode.productLabel', { name: product.name }) }}</p>
            <p class="text-sm text-muted-foreground font-mono" v-if="product.barcode">
                {{ t('inventory.barcode.currentBarcode', { barcode: product.barcode }) }}
            </p>
        </div>

        <div class="rounded-md border p-6 space-y-4">
            <div class="grid gap-2">
                <Label for="barcode">{{ t('inventory.barcode.value') }}</Label>
                <Input id="barcode" v-model="barcodeValue" :placeholder="t('inventory.barcode.enterNumber')" class="font-mono" />
            </div>

            <div class="grid gap-2">
                <Label for="quantity">{{ t('common.quantity') }}</Label>
                <Input id="quantity" v-model.number="quantity" type="number" min="1" max="100" />
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2">
                    <Checkbox :checked="includePrice" @update:checked="includePrice = $event" />
                    <span class="text-sm">{{ t('inventory.barcode.includePrice') }}</span>
                </label>
                <label class="flex items-center gap-2">
                    <Checkbox :checked="includeName" @update:checked="includeName = $event" />
                    <span class="text-sm">{{ t('inventory.barcode.includeName') }}</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button @click="printBarcodes" :disabled="!barcodeValue">{{ t('inventory.barcode.generatePrint') }}</Button>
                <Button variant="outline" as-child :disabled="!barcodeUrl">
                    <a v-if="barcodeUrl" :href="barcodeUrl" target="_blank">{{ t('inventory.barcode.viewPdf') }}</a>
                    <span v-else>{{ t('inventory.barcode.viewPdf') }}</span>
                </Button>
            </div>
        </div>

        <div v-if="barcodeUrl" class="rounded-md border p-6 flex justify-center bg-white dark:bg-white/5">
            <img :src="barcodeUrl" alt="Barcode preview" class="max-w-full" />
        </div>
    </div>
</template>
