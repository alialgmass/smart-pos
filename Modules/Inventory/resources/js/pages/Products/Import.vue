<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
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
import { ref } from 'vue'
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

const form = useForm({
    file: null as File | null,
})

const preview = ref<ImportRow[]>([])
const importMode = ref<'preview' | 'confirm'>('preview')
import { Spinner } from '@/components/ui/spinner'

const handleUpload = () => {
    if (!form.file) return

    const data = new FormData()
    data.append('file', form.file)

    form.post(previewRoute.url(), {
        preserveScroll: true,
        onSuccess: (page: any) => {
            preview.value = page.props.flash?.importPreview ?? []
            importMode.value = 'preview'
        },
    })
}

const confirmImport = () => {
    const data = new FormData()
    if (form.file) {
        data.append('file', form.file)
    }

    useForm({}).post(confirmRoute.url(), {
        data,
        preserveScroll: true,
        onSuccess: () => {
            preview.value = []
            form.file = null
            importMode.value = 'preview'
        },
    })
}

const validCount = preview.value.filter((r) => r.status === 'valid').length
const invalidCount = preview.value.filter((r) => r.status === 'invalid').length
</script>

<template>
    <Head title="Import Products" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Import Products</h1>
                <p class="text-sm text-muted-foreground">Import products from a spreadsheet file.</p>
            </div>
            <Button variant="outline" as-child>
                <a :href="'/inventory/products/import/template'" download>Download Template</a>
            </Button>
        </div>

        <div class="rounded-md border p-6">
            <h2 class="font-semibold mb-4">Upload File</h2>
            <div class="flex items-center gap-4">
                <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                    @change="(e: any) => { form.file = e.target.files?.[0] ?? null }"
                />
                <Button @click="handleUpload" :disabled="!form.file || form.processing">
                    <Spinner v-if="form.processing" />
                    Preview
                </Button>
            </div>
            <p class="text-xs text-muted-foreground mt-2">Supported formats: .xlsx, .xls, .csv</p>
        </div>

        <div v-if="preview.length > 0" class="rounded-md border">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <div>
                    <h2 class="font-semibold">Preview Results</h2>
                    <p class="text-sm text-muted-foreground">
                        <Badge variant="success" class="mr-1">{{ validCount }} valid</Badge>
                        <Badge v-if="invalidCount > 0" variant="warning">{{ invalidCount }} with errors</Badge>
                    </p>
                </div>
                <Button @click="confirmImport" :disabled="validCount === 0">
                    Import {{ validCount }} Products
                </Button>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Barcode</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Stock</TableHead>
                        <TableHead>Status</TableHead>
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
                                {{ row.status === 'valid' ? 'Valid' : 'Invalid' }}
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
