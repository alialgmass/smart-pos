<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
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
    DialogTrigger,
} from '@/components/ui/dialog'
import { ref, computed } from 'vue'
import { Spinner } from '@/components/ui/spinner'
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
    if (days > 30) return { variant: 'destructive' as const, label: `${days} Days Overdue` }
    if (days > 15) return { variant: 'warning' as const, label: `${days} Days Overdue` }
    return { variant: 'secondary' as const, label: `${days} Days Overdue` }
}

const whatsappUrl = (phone: string) => {
    const cleaned = phone.replace(/[^0-9]/g, '')
    return `https://wa.me/${cleaned}`
}
</script>

<template>
    <Head title="Debt Management" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Debt Management</h1>
                <p class="text-sm text-muted-foreground">Oversee outstanding customer balances and collection status.</p>
            </div>
            <div class="rounded-md border p-3 bg-muted/30">
                <p class="text-xs text-muted-foreground">Total Outstanding</p>
                <p class="text-xl font-bold font-mono text-destructive">{{ Number(summary.total_outstanding).toFixed(2) }} EGP</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-md border p-4">
                <div class="flex justify-between items-start">
                    <p class="text-sm text-muted-foreground">Critical Overdue (&gt;30 Days)</p>
                    <Badge variant="destructive">URGENT</Badge>
                </div>
                <p class="text-3xl font-bold mt-2">{{ summary.critical_overdue_count }}</p>
                <p class="text-xs text-muted-foreground">customers</p>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex justify-between items-start">
                    <p class="text-sm text-muted-foreground">Pending Reminders</p>
                    <Badge variant="warning">WARNING</Badge>
                </div>
                <p class="text-3xl font-bold mt-2">{{ summary.pending_reminders }}</p>
                <p class="text-xs text-muted-foreground">automated alerts scheduled</p>
            </div>
            <div class="rounded-md border p-4 bg-secondary/10 border-secondary/30">
                <p class="text-sm text-muted-foreground">Collected This Week</p>
                <p class="text-3xl font-bold mt-2 font-mono text-secondary">{{ Number(summary.collected_this_week).toFixed(2) }} EGP</p>
                <p class="text-xs text-muted-foreground">+14% vs last week</p>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <h2 class="font-semibold">Customer Ledger</h2>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm">Filter</Button>
                    <Button variant="outline" size="sm">Export</Button>
                </div>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Customer</TableHead>
                        <TableHead>Total Debt</TableHead>
                        <TableHead>Days Overdue</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="customer in customers" :key="customer.id">
                        <TableCell>
                            <div>
                                <p class="font-medium">{{ customer.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ customer.phone }}</p>
                            </div>
                        </TableCell>
                        <TableCell class="font-mono font-bold">{{ Number(customer.debt_balance).toFixed(2) }} EGP</TableCell>
                        <TableCell>
                            <Badge :variant="overdueBadge(customer.days_overdue).variant">
                                {{ overdueBadge(customer.days_overdue).label }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Button size="sm" @click="openPaymentModal(customer)">Record Payment</Button>
                                <Button variant="outline" size="sm" as-child>
                                    <a :href="whatsappUrl(customer.phone)" target="_blank">WhatsApp</a>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="customers.length === 0">
                        <TableCell colspan="4" class="text-center text-muted-foreground py-8">
                            No customers with outstanding debt.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>

    <Dialog :open="showPaymentModal" @update:open="showPaymentModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Record Debt Payment</DialogTitle>
                <DialogDescription>Record a payment for this customer's outstanding debt.</DialogDescription>
            </DialogHeader>

            <div v-if="selectedCustomer" class="space-y-4">
                <div class="rounded-md border p-3 bg-muted/30">
                    <p class="text-xs text-muted-foreground">Customer</p>
                    <p class="font-medium">{{ selectedCustomer.name }}</p>
                    <p class="font-mono text-sm text-destructive">Debt: {{ Number(selectedCustomer.debt_balance).toFixed(2) }} EGP</p>
                </div>

                <div class="grid gap-2">
                    <Label for="amount">Amount Received (EGP)</Label>
                    <Input id="amount" v-model="paymentForm.amount" type="number" step="0.01" placeholder="0.00" class="text-lg font-mono" />
                </div>

                <div class="grid gap-2">
                    <Label>Payment Method</Label>
                    <div class="grid grid-cols-3 gap-2">
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'cash' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'cash'"
                        >
                            Cash
                        </Button>
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'card' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'card'"
                        >
                            Card
                        </Button>
                        <Button
                            type="button"
                            :variant="paymentForm.payment_method === 'wallet' ? 'default' : 'outline'"
                            @click="paymentForm.payment_method = 'wallet'"
                        >
                            Wallet
                        </Button>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Notes (Optional)</Label>
                    <textarea
                        id="notes"
                        v-model="paymentForm.notes"
                        class="flex h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="e.g. Partial payment for June invoices"
                    ></textarea>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="showPaymentModal = false">Cancel</Button>
                <Button @click="submitPayment" :disabled="paymentForm.processing">
                    <Spinner v-if="paymentForm.processing" />
                    Confirm Payment
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
