<script setup lang="ts">
import { Label } from '@/components/ui/label'
import { search as customerSearch } from '@/routes/customers'
import axios from 'axios'
import {
    AlertCircle,
    ArrowRight,
    Banknote,
    CalendarClock,
    CheckCircle2,
    CreditCard,
    Info,
    Search,
    User,
    Wallet,
} from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'

interface Customer {
    id: number
    name: string
    phone: string
    debt_balance: string
    loyalty_points: number
}

const { t } = useI18n()

const props = defineProps<{
    total: number
}>()

const emit = defineEmits<{
    complete: [data: {
        paymentMethod: number
        paidAmount: number
        changeAmount: number
        cashAmount?: number
        cardAmount?: number
        customerId?: number
        customerName?: string
        dueDate?: string
    }]
    close: []
}>()

const activeTab = ref(1)
const paidAmount = ref(props.total)
const cashAmount = ref(0)
const cardAmount = ref(0)
const calculatedChange = computed(() => Math.max(0, paidAmount.value - props.total))
const mixedRemaining = computed(() => {
    const totalPaid = (cashAmount.value || 0) + (cardAmount.value || 0)

    return Math.max(0, props.total - totalPaid)
})

// Deferred payment state
const customerQuery = ref('')
const customerResults = ref<Customer[]>([])
const selectedCustomer = ref<Customer | null>(null)
const searching = ref(false)
const showResults = ref(false)

const DEFFERED_METHOD_ID = 4
const dueDate = ref('')

watch(customerQuery, async (query) => {
    if (activeTab.value !== DEFFERED_METHOD_ID || !query || query.length < 1) {
        customerResults.value = []
        showResults.value = false

        return
    }

    searching.value = true
    showResults.value = true

    try {
        const res = await axios.get(customerSearch.url(), {
            params: { q: query },
        })
        customerResults.value = res.data.customers ?? []
    } catch {
        customerResults.value = []
    } finally {
        searching.value = false
    }
})

function selectCustomer(customer: Customer) {
    selectedCustomer.value = customer
    customerQuery.value = customer.name
    showResults.value = false
}

function clearCustomer() {
    selectedCustomer.value = null
    customerQuery.value = ''
    customerResults.value = []
}

function handleConfirm() {
    if (activeTab.value === DEFFERED_METHOD_ID) {
        emit('complete', {
            paymentMethod: DEFFERED_METHOD_ID,
            paidAmount: props.total,
            changeAmount: 0,
            customerId: selectedCustomer.value?.id,
            customerName: selectedCustomer.value?.name,
            dueDate: dueDate.value || undefined,
        })
    } else if (activeTab.value === 3) {
        // Mixed payment
        emit('complete', {
            paymentMethod: 3,
            paidAmount: (cashAmount.value || 0) + (cardAmount.value || 0),
            changeAmount: 0,
            cashAmount: cashAmount.value,
            cardAmount: cardAmount.value,
        })
    } else {
        emit('complete', {
            paymentMethod: activeTab.value,
            paidAmount: paidAmount.value,
            changeAmount: calculatedChange.value,
        })
    }
}

function handleClose() {
    emit('close')
}

const canConfirm = computed(() => {
    switch (activeTab.value) {
        case DEFFERED_METHOD_ID:
            return !!selectedCustomer.value
        case 3: // Mixed
            return (cashAmount.value || 0) + (cardAmount.value || 0) >= props.total
        default:
            return paidAmount.value > 0 && paidAmount.value >= props.total
    }
})

const tabs = computed(() => [
    { id: 1, label: t('sales::payment.cash'), icon: Banknote },
    { id: 2, label: t('sales::payment.card'), icon: CreditCard },
    { id: 3, label: t('sales::payment.mixed'), icon: Wallet },
    { id: 4, label: t('sales::payment.deferred'), icon: CalendarClock },
])
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="flex max-h-[92vh] w-[700px] max-w-[95vw] flex-col overflow-hidden rounded-xl border border-border bg-card shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between gap-6 bg-primary p-5 text-primary-foreground">
                <div>
                    <h2 class="text-lg font-semibold">{{ t('sales::payment.title') }}</h2>
                    <div v-if="selectedCustomer" class="mt-1.5 flex items-center gap-1.5 opacity-80">
                        <User class="size-4" />
                        <p class="text-sm">
                            {{ t('sales::payment.customer') }}:
                            <span class="font-bold underline">{{ selectedCustomer.name }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-end">
                    <p class="text-xs font-medium opacity-80">{{ t('sales::payment.payable_amount') }}</p>
                    <p class="text-3xl leading-none font-bold tabular-nums">{{ Number(total).toFixed(2) }} <span class="text-base">EGP</span></p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-border bg-muted/50">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    class="flex flex-1 flex-col items-center gap-1 px-4 py-4 transition-colors"
                    :class="activeTab === tab.id
                        ? 'border-b-4 border-primary text-primary'
                        : 'border-b-4 border-transparent text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                    @click="activeTab = tab.id"
                >
                    <component :is="tab.icon" class="size-5" />
                    <span class="text-sm" :class="activeTab === tab.id ? 'font-bold' : 'font-medium'">{{ tab.label }}</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="grow space-y-5 overflow-y-auto p-6">
                <!-- Cash Tab -->
                <div v-if="activeTab === 1" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label class="block font-bold">{{ t('sales::payment.amount_received') }}</Label>
                        <div class="relative">
                            <Banknote class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded-xl border-2 border-input bg-background py-4 pe-12 ps-10 text-lg tabular-nums transition-colors outline-none focus:border-ring"
                                placeholder="0.00"
                            />
                            <span class="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">EGP</span>
                        </div>
                    </div>
                    <div v-if="calculatedChange > 0" class="flex items-center justify-between rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                        <span class="text-sm font-bold">{{ t('sales::payment.change_due') }}</span>
                        <span class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">{{ calculatedChange.toFixed(2) }} EGP</span>
                    </div>
                </div>

                <!-- Card Tab -->
                <div v-if="activeTab === 2" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label class="block font-bold">{{ t('sales::payment.card_amount') }}</Label>
                        <div class="relative">
                            <CreditCard class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded-xl border-2 border-input bg-background py-4 pe-12 ps-10 text-lg tabular-nums transition-colors outline-none focus:border-ring"
                                placeholder="0.00"
                            />
                            <span class="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">EGP</span>
                        </div>
                    </div>
                </div>

                <!-- Mixed Tab -->
                <div v-if="activeTab === 3" class="space-y-5">
                    <div class="flex items-start gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
                        <Info class="mt-0.5 size-4 shrink-0 text-primary" />
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ t('sales::payment.split_hint') }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <Label class="block font-bold">{{ t('sales::payment.cash_amount') }}</Label>
                            <div class="relative">
                                <Banknote class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <input
                                    v-model.number="cashAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-xl border-2 border-input bg-background py-4 pe-12 ps-10 text-lg tabular-nums transition-colors outline-none focus:border-ring"
                                    placeholder="0.00"
                                />
                                <span class="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">EGP</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="block font-bold">{{ t('sales::payment.card_amount') }}</Label>
                            <div class="relative">
                                <CreditCard class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <input
                                    v-model.number="cardAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-xl border-2 border-input bg-background py-4 pe-12 ps-10 text-lg tabular-nums transition-colors outline-none focus:border-ring"
                                    placeholder="0.00"
                                />
                                <span class="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">EGP</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-xl p-4"
                        :class="mixedRemaining <= 0 ? 'border border-emerald-500/30 bg-emerald-500/10' : 'border border-destructive/30 bg-destructive/10'"
                    >
                        <div class="flex items-center gap-2">
                            <component
                                :is="mixedRemaining <= 0 ? CheckCircle2 : AlertCircle"
                                class="size-4"
                                :class="mixedRemaining <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive'"
                            />
                            <span
                                class="text-sm font-bold tracking-wide uppercase"
                                :class="mixedRemaining <= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-destructive'"
                            >
                                {{ mixedRemaining <= 0 ? t('sales::payment.ready_to_finalize') : t('sales::payment.remaining_balance') }}
                            </span>
                        </div>
                        <p class="text-lg font-semibold tabular-nums" :class="mixedRemaining <= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-destructive'">
                            {{ Math.max(0, mixedRemaining).toFixed(2) }} EGP
                        </p>
                    </div>
                </div>

                <!-- Deferred Tab -->
                <div v-if="activeTab === DEFFERED_METHOD_ID" class="space-y-4">
                    <div class="flex items-start gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
                        <Info class="mt-0.5 size-4 shrink-0 text-primary" />
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ t('sales::payment.deferred_hint') }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="block font-bold">
                            {{ t('sales::payment.customer') }}
                            <span class="text-destructive">*</span>
                        </Label>

                        <div v-if="!selectedCustomer" class="relative">
                            <div class="relative">
                                <Search class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <input
                                    v-model="customerQuery"
                                    type="text"
                                    class="w-full rounded-xl border-2 border-input bg-background py-4 pe-4 ps-10 transition-colors outline-none focus:border-ring"
                                    :placeholder="t('sales::payment.search_customer')"
                                    autocomplete="off"
                                />
                            </div>
                            <div
                                v-if="showResults && customerQuery.length > 0"
                                class="absolute inset-x-0 top-full z-10 mt-1 max-h-48 overflow-y-auto rounded-xl border border-border bg-popover shadow-lg"
                            >
                                <div v-if="searching" class="px-4 py-3 text-center text-sm text-muted-foreground">
                                    {{ t('sales::payment.searching') }}
                                </div>
                                <div
                                    v-else-if="customerResults.length === 0"
                                    class="px-4 py-3 text-center text-sm text-muted-foreground"
                                >
                                    {{ t('sales::payment.no_customers') }}
                                </div>
                                <button
                                    v-for="customer in customerResults"
                                    :key="customer.id"
                                    class="flex w-full items-center justify-between px-4 py-3 text-start text-sm transition-colors hover:bg-accent"
                                    @click="selectCustomer(customer)"
                                >
                                    <span class="font-medium">{{ customer.name }}</span>
                                    <span class="text-muted-foreground">{{ customer.phone }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-center justify-between rounded-xl border border-border bg-muted/50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">
                                    {{ selectedCustomer.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ selectedCustomer.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ selectedCustomer.phone }}</p>
                                </div>
                            </div>
                            <button class="text-sm text-primary hover:underline" @click="clearCustomer">
                                {{ t('sales::payment.change') }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="block font-bold">{{ t('sales::payment.due_date_optional') }}</Label>
                        <input
                            v-model="dueDate"
                            type="date"
                            class="w-full rounded-xl border-2 border-input bg-background px-4 py-4 transition-colors outline-none focus:border-ring"
                        />
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="size-4 text-emerald-600 dark:text-emerald-400" />
                            <span class="text-sm font-bold tracking-wide uppercase text-emerald-700 dark:text-emerald-400">
                                {{ t('sales::payment.ready_to_record') }}
                            </span>
                        </div>
                        <div class="text-end">
                            <p class="text-xs font-bold uppercase text-emerald-700 dark:text-emerald-400">{{ t('sales::payment.amount_to_defer') }}</p>
                            <p class="text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">{{ Number(total).toFixed(2) }} EGP</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex gap-4 border-t border-border bg-muted/50 p-5">
                <button
                    class="flex-1 rounded-xl border-2 border-border py-4 font-bold transition-all hover:bg-accent active:scale-[0.98]"
                    @click="handleClose"
                >
                    {{ t('sales::payment.cancel_transaction') }}
                </button>
                <button
                    class="flex-[2] items-center justify-center gap-2 rounded-xl bg-primary py-4 text-lg font-bold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50"
                    :disabled="!canConfirm"
                    @click="handleConfirm"
                >
                    <span class="inline-flex items-center justify-center gap-2">
                        {{ activeTab === DEFFERED_METHOD_ID ? t('sales::payment.record_debt') : t('sales::payment.complete_payment') }}
                        <ArrowRight class="size-5 rtl:-scale-x-100" />
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
