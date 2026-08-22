<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { ref } from 'vue'
import PageHeader from '@/components/shared/PageHeader.vue'
import EmptyState from '@/components/shared/EmptyState.vue'
import { HandCoins, MessageCircle, ReceiptText, Download } from 'lucide-vue-next'
import { store as paymentStore } from '@/routes/customers/debt-payments'

interface DebtCustomer {
    id: number
    name: string
    phone: string
    debt_balance: string
    days_overdue: number
}

interface DebtSummary {
    total_outstanding: string
    critical_overdue_count: number
    pending_reminders: number
    collected_this_week: string
}

const { t } = useI18n()

const props = defineProps<{
    customers: DebtCustomer[]
    summary: DebtSummary
}>()

const paymentForm = useForm({
    customer_id: 0,
    amount: '',
    payment_method: 'cash',
    notes: '',
})

const selectedCustomer = ref<DebtCustomer | null>(null)
const showPaymentModal = ref(false)

const openPaymentModal = (customer: DebtCustomer) => {
    selectedCustomer.value = customer
    paymentForm.customer_id = customer.id
    paymentForm.amount = ''
    paymentForm.payment_method = 'cash'
    paymentForm.notes = ''
    showPaymentModal.value = true
}

const submitPayment = () => {
    paymentForm.post(paymentStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false
        },
    })
}

const overdueBadge = (days: number) => {
    const label = t('customers::debts.days_overdue_n', { days })
    if (days > 30) return { variant: 'destructive' as const, label }
    if (days > 15) return { variant: 'warning' as const, label }
    return { variant: 'secondary' as const, label }
}

const whatsappUrl = (phone: string) => {
    const cleaned = phone.replace(/[^0-9]/g, '')
    return `https://wa.me/${cleaned}`
}
</script>

<template>
    <Head :title="t('customers::debts.title')" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <PageHeader
                :title="t('customers::debts.title')"
                :description="t('customers::debts.subtitle')"
            />
            <div class="rounded-lg border bg-muted/30 p-3">
                <p class="text-xs text-muted-foreground">{{ t('customers::debts.total_outstanding') }}</p>
                <p class="font-mono text-xl font-bold tabular-nums text-destructive">{{ Number(summary.total_outstanding).toFixed(2) }} EGP</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border bg-card p-4">
                <div class="flex items-start justify-between">
                    <p class="text-sm text-muted-foreground">{{ t('customers::debts.critical_overdue') }}</p>
                    <Badge variant="destructive">{{ t('customers::debts.urgent') }}</Badge>
                </div>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ summary.critical_overdue_count }}</p>
                <p class="text-xs text-muted-foreground">{{ t('customers::debts.customers_count') }}</p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <div class="flex items-start justify-between">
                    <p class="text-sm text-muted-foreground">{{ t('customers::debts.pending_reminders') }}</p>
                    <Badge variant="warning">{{ t('customers::debts.warning') }}</Badge>
                </div>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ summary.pending_reminders }}</p>
                <p class="text-xs text-muted-foreground">{{ t('customers::debts.automated_alerts') }}</p>
            </div>
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-4">
                <p class="text-sm text-muted-foreground">{{ t('customers::debts.collected_this_week') }}</p>
                <p class="mt-2 font-mono text-3xl font-bold tabular-nums text-emerald-700 dark:text-emerald-400">{{ Number(summary.collected_this_week).toFixed(2) }} EGP</p>
                <p class="text-xs text-muted-foreground">{{ t('customers::debts.vs_last_week') }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <h2 class="font-semibold">{{ t('customers::debts.customer_ledger') }}</h2>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm">{{ t('customers::debts.filter') }}</Button>
                    <Button variant="outline" size="sm">
                        <Download class="size-3.5" />
                        {{ t('customers::debts.export') }}
                    </Button>
                </div>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('customers::debts.customer') }}</TableHead>
                        <TableHead>{{ t('customers::debts.total_debt') }}</TableHead>
                        <TableHead>{{ t('customers::debts.days_overdue') }}</TableHead>
                        <TableHead class="text-end">{{ t('customers::show.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="customer in customers" :key="customer.id">
                        <TableCell>
                            <div>
                                <p class="font-medium">{{ customer.name }}</p>
                                <p class="text-xs text-muted-foreground tabular-nums" dir="ltr">{{ customer.phone }}</p>
                            </div>
                        </TableCell>
                        <TableCell class="font-mono font-bold tabular-nums">{{ Number(customer.debt_balance).toFixed(2) }} EGP</TableCell>
                        <TableCell>
                            <Badge :variant="overdueBadge(customer.days_overdue).variant">
                                {{ overdueBadge(customer.days_overdue).label }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-end">
                            <div class="flex justify-end gap-2">
                                <Button size="sm" @click="openPaymentModal(customer)">
                                    <HandCoins class="size-3.5" />
                                    {{ t('customers::debts.record_payment') }}
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <a :href="whatsappUrl(customer.phone)" target="_blank" rel="noopener">
                                        <MessageCircle class="size-3.5" />
                                        {{ t('customers::debts.whatsapp') }}
                                    </a>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="customers.length === 0">
                        <TableCell colspan="4" class="py-8 text-center text-muted-foreground">
                            {{ t('customers::debts.no_debt_customers') }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>

    <Dialog :open="showPaymentModal" @update:open="showPaymentModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ t('customers::debts.record_title') }}</DialogTitle>
                <DialogDescription>{{ t('customers::debts.record_description') }}</DialogDescription>
            </DialogHeader>

            <div v-if="selectedCustomer" class="space-y-4">
                <div class="rounded-lg border bg-muted/30 p-3">
                    <p class="text-xs text-muted-foreground">{{ t('customers::debts.customer_label') }}</p>
                    <p class="font-medium">{{ selectedCustomer.name }}</p>
                    <p class="font-mono text-sm tabular-nums text-destructive">
                        {{ t('customers::debts.total_debt') }}: {{ Number(selectedCustomer.debt_balance).toFixed(2) }} EGP
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="amount">{{ t('customers::debts.amount_received') }}</Label>
                    <Input id="amount" v-model="paymentForm.amount" type="number" step="0.01" placeholder="0.00" class="font-mono text-lg" />
                </div>

                <div class="grid gap-2">
                    <Label>{{ t('customers::show.payment_method') }}</Label>
                    <div class="grid grid-cols-3 gap-2">
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'cash' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'cash'"
                        >
                            {{ t('customers::debts.cash') }}
                        </Button>
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'card' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'card'"
                        >
                            {{ t('customers::debts.card') }}
                        </Button>
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'wallet' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'wallet'"
                        >
                            {{ t('customers::debts.wallet') }}
                        </Button>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="notes">{{ t('customers::debts.notes_optional') }}</Label>
                    <textarea
                        id="notes"
                        v-model="paymentForm.notes"
                        class="h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30"
                        :placeholder="t('customers::debts.notes_placeholder')"
                    ></textarea>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="showPaymentModal = false">{{ t('customers::debts.cancel') }}</Button>
                <Button @click="submitPayment" :disabled="paymentForm.processing">
                    {{ t('customers::debts.confirm_payment') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
