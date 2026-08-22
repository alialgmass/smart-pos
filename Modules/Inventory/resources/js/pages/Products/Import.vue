<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Spinner } from '@/components/ui/spinner'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { PageHeader } from '@/components/shared'
import { preview as previewRoute, confirm as confirmRoute } from '@/routes/inventory/products/import'

interface ImportRow {
    row: number
    name: string
    barcode: string
    price: string
    stock_qty: string
    status: 'valid' | 'invalid'
    errors: string[]
}

const { t } = useI18n()

const form = useForm({
    file: null as File | null,
})

const preview = ref<ImportRow[]>([])
const importMode = ref<'preview' | 'confirm'>('preview')

const handleUpload = () => {
    if (!form.file) return

    form.post(previewRoute.url(), {
        preserveScroll: true,
        onSuccess: (page: any) => {
            preview.value = page.props.flash?.importPreview ?? []
            importMode.value = 'preview'
        },
    })
}

const confirmImport = () => {
    if (!form.file) return

    form.post(confirmRoute.url(), {
        preserveScroll: true,
        onSuccess: () => {
            preview.value = []
            form.reset()
            importMode.value = 'preview'
        },
    })
}

const validCount = preview.value.filter((r) => r.status === 'valid').length
const invalidCount = preview.value.filter((r) => r.status === 'invalid').length
</script>

<template>
    <Head :title="t('inventory.import.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('inventory.import.title')" :description="t('inventory.import.description')">
            <template #actions>
                <Button variant="outline" as-child>
                    <Link href="/inventory/products">{{ t('common.back') }}</Link>
                </Button>
                <Button variant="outline" as-child>
                    <a href="/inventory/products/import/template" download>{{ t('inventory.import.downloadTemplate') }}</a>
                </Button>
            </template>
        </PageHeader>

        <div class="rounded-md border p-6">
            <h2 class="font-semibold mb-4">{{ t('inventory.import.uploadFile') }}</h2>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                    @change="(e: any) => { form.file = e.target.files?.[0] ?? null }"
                />
                <Button @click="handleUpload" :disabled="!form.file || form.processing">
                    <Spinner v-if="form.processing" />
                    {{ t('inventory.import.preview') }}
                </Button>
            </div>
            <p class="text-xs text-muted-foreground mt-2">{{ t('inventory.import.formats') }}</p>
        </div>

        <div v-if="preview.length > 0" class="rounded-md border">
            <div class="px-4 py-3 border-b flex flex-wrap justify-between items-center gap-3">
                <div>
                    <h2 class="font-semibold">{{ t('inventory.import.results') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1 flex items-center gap-1">
                        <Badge variant="success">{{ t('inventory.import.validCount', { count: validCount }) }}</Badge>
                        <Badge v-if="invalidCount > 0" variant="warning">
                            {{ t('inventory.import.invalidCount', { count: invalidCount }) }}
                        </Badge>
                    </p>
                </div>
                <Button @click="confirmImport" :disabled="validCount === 0 || form.processing">
                    <Spinner v-if="form.processing" />
                    {{ t('inventory.import.importCount', { count: validCount }) }}
                </Button>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>{{ t('common.name') }}</TableHead>
                        <TableHead>{{ t('inventory.products.barcode') }}</TableHead>
                        <TableHead>{{ t('common.price') }}</TableHead>
                        <TableHead>{{ t('inventory.variants.stock') }}</TableHead>
                        <TableHead>{{ t('common.status') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in preview" :key="row.row">
                        <TableCell>{{ row.row }}</TableCell>
                        <TableCell>{{ row.name }}</TableCell>
                        <TableCell class="font-mono text-sm">{{ row.barcode }}</TableCell>
                        <TableCell class="font-mono">{{ row.price }}</TableCell>
                        <TableCell>{{ row.stock_qty }}</TableCell>
                        <TableCell>
                            <Badge :variant="row.status === 'valid' ? 'success' : 'warning'">
                                {{ row.status === 'valid' ? t('inventory.import.rowValid') : t('inventory.import.rowInvalid') }}
                            </Badge>
                            <p v-if="row.errors.length" class="text-xs text-destructive mt-1">
                                {{ row.errors.join(', ') }}
                            </p>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
