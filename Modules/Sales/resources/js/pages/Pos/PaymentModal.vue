<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import { search as customerSearch } from '@/routes/customers'
import axios from 'axios'

interface Customer {
    id: number
    name: string
    phone: string
    debt_balance: string
    loyalty_points: number
}

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

const tabs = [
    { id: 1, label: 'Cash', icon: 'payments' },
    { id: 2, label: 'Card', icon: 'credit_card' },
    { id: 3, label: 'Mixed', icon: 'account_balance_wallet' },
    { id: 4, label: 'Deferred (آجل)', icon: 'event_repeat' },
]
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/40 backdrop-blur-sm">
        <div class="bg-surface-container-lowest w-[700px] max-w-[95vw] rounded-xl shadow-2xl overflow-hidden flex flex-col border border-outline-variant">
            <!-- Modal Header -->
            <div class="bg-primary p-lg text-on-primary flex justify-between items-center">
                <div>
                    <h2 class="text-headline-md font-headline-md">Payment Details</h2>
                    <div v-if="selectedCustomer" class="flex items-center gap-sm mt-xs opacity-80">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        <p class="text-label-md">Customer: <span class="font-bold underline">{{ selectedCustomer.name }}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-label-md font-medium opacity-80">Payable Amount</p>
                    <p class="text-display-lg font-numeric-pos leading-none">{{ Number(total).toFixed(2) }} <span class="text-headline-sm">EGP</span></p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex bg-surface-container-low border-b border-outline-variant">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    class="flex-1 py-lg px-md flex flex-col items-center gap-sm transition-colors"
                    :class="activeTab === tab.id
                        ? 'border-b-4 border-primary text-primary font-bold'
                        : 'border-b-4 border-transparent text-on-surface-variant hover:bg-surface-container'"
                    @click="activeTab = tab.id"
                >
                    <span class="material-symbols-outlined">{{ tab.icon }}</span>
                    <span class="font-label-md">{{ tab.label }}</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-xl space-y-lg flex-grow">
                <!-- Cash Tab -->
                <div v-if="activeTab === 1" class="space-y-4">
                    <div class="space-y-sm">
                        <Label class="block font-label-md text-on-surface font-bold">Amount Received</Label>
                        <div class="relative group">
                            <div class="absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">payments</div>
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full pl-xl pr-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-numeric-pos text-headline-sm transition-all outline-none"
                                placeholder="0.00"
                            />
                            <span class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant font-label-md">EGP</span>
                        </div>
                    </div>
                    <div v-if="calculatedChange > 0" class="flex items-center justify-between p-md bg-secondary-container/20 rounded-xl border border-secondary-container">
                        <span class="font-label-md font-bold text-on-secondary-container">Change Due</span>
                        <span class="font-numeric-pos text-secondary text-headline-sm">{{ calculatedChange.toFixed(2) }} EGP</span>
                    </div>
                </div>

                <!-- Card Tab -->
                <div v-if="activeTab === 2" class="space-y-4">
                    <div class="space-y-sm">
                        <Label class="block font-label-md text-on-surface font-bold">Card Amount</Label>
                        <div class="relative group">
                            <div class="absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">credit_card</div>
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full pl-xl pr-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-numeric-pos text-headline-sm transition-all outline-none"
                                placeholder="0.00"
                            />
                            <span class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant font-label-md">EGP</span>
                        </div>
                    </div>
                </div>

                <!-- Mixed Tab -->
                <div v-if="activeTab === 3" class="space-y-lg">
                    <div class="bg-primary-container/10 p-md rounded-lg border border-primary-container/20 flex items-start gap-md">
                        <span class="material-symbols-outlined text-primary-container">info</span>
                        <p class="text-label-md text-on-primary-fixed-variant leading-relaxed">
                            Split the total amount across multiple payment methods. Ensure the sum of all fields equals the total payable amount.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-lg">
                        <div class="space-y-sm">
                            <Label class="block font-label-md text-on-surface font-bold">Cash Amount</Label>
                            <div class="relative group">
                                <div class="absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">payments</div>
                                <input
                                    v-model.number="cashAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-xl pr-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-numeric-pos text-headline-sm transition-all outline-none"
                                    placeholder="0.00"
                                />
                                <span class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant font-label-md">EGP</span>
                            </div>
                        </div>
                        <div class="space-y-sm">
                            <Label class="block font-label-md text-on-surface font-bold">Card Amount</Label>
                            <div class="relative group">
                                <div class="absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">credit_card</div>
                                <input
                                    v-model.number="cardAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-xl pr-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-numeric-pos text-headline-sm transition-all outline-none"
                                    placeholder="0.00"
                                />
                                <span class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant font-label-md">EGP</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-md rounded-xl"
                        :class="mixedRemaining <= 0 ? 'bg-secondary-container/20 border border-secondary-container' : 'bg-error-container/20 border border-error-container/30'">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined" :class="mixedRemaining <= 0 ? 'text-secondary' : 'text-error'">
                                {{ mixedRemaining <= 0 ? 'check_circle' : 'error' }}
                            </span>
                            <span class="font-label-md font-bold uppercase tracking-wide" :class="mixedRemaining <= 0 ? 'text-on-secondary-container' : 'text-on-error-container'">
                                {{ mixedRemaining <= 0 ? 'Ready to Finalize' : 'Remaining Balance' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="font-numeric-pos text-headline-sm" :class="mixedRemaining <= 0 ? 'text-secondary' : 'text-error'">
                                {{ Math.max(0, mixedRemaining).toFixed(2) }} EGP
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Deferred Tab (T096) -->
                <div v-if="activeTab === DEFFERED_METHOD_ID" class="space-y-4">
                    <div class="bg-primary-container/10 p-md rounded-lg border border-primary-container/20 flex items-start gap-md">
                        <span class="material-symbols-outlined text-primary-container">info</span>
                        <p class="text-label-md text-on-primary-fixed-variant leading-relaxed">
                            Deferred payment will be recorded as a debt for this customer. Select a customer to continue.
                        </p>
                    </div>

                    <div class="space-y-sm">
                        <Label class="block font-label-md text-on-surface font-bold">Customer <span class="text-error">*</span></Label>

                        <div v-if="!selectedCustomer" class="relative">
                            <div class="relative">
                                <div class="absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">search</div>
                                <input
                                    v-model="customerQuery"
                                    type="text"
                                    class="w-full pl-xl pr-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-body-md transition-all outline-none"
                                    placeholder="Search by name or phone..."
                                    autocomplete="off"
                                />
                            </div>
                            <div
                                v-if="showResults && customerQuery.length > 0"
                                class="absolute top-full left-0 right-0 mt-1 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg max-h-48 overflow-y-auto z-10"
                            >
                                <div v-if="searching" class="px-4 py-3 text-sm text-muted-foreground text-center">
                                    Searching...
                                </div>
                                <div
                                    v-else-if="customerResults.length === 0"
                                    class="px-4 py-3 text-sm text-muted-foreground text-center"
                                >
                                    No customers found
                                </div>
                                <button
                                    v-for="customer in customerResults"
                                    :key="customer.id"
                                    class="w-full px-4 py-3 text-left text-sm hover:bg-surface-container flex items-center justify-between transition-colors"
                                    @click="selectCustomer(customer)"
                                >
                                    <span class="font-medium">{{ customer.name }}</span>
                                    <span class="text-muted-foreground">{{ customer.phone }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-center justify-between bg-surface-container-low rounded-xl px-4 py-3 border border-outline-variant">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-primary font-bold">
                                    {{ selectedCustomer.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ selectedCustomer.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ selectedCustomer.phone }}</p>
                                </div>
                            </div>
                            <button class="text-primary hover:underline font-label-md text-sm" @click="clearCustomer">
                                Change
                            </button>
                        </div>
                    </div>

                    <div class="space-y-sm">
                        <Label class="block font-label-md text-on-surface font-bold">Payment Due Date (Optional)</Label>
                        <input
                            v-model="dueDate"
                            type="date"
                            class="w-full px-md py-lg bg-surface-container-lowest border-2 border-outline-variant focus:border-primary rounded-xl font-body-md transition-all outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-between p-md bg-secondary-container/20 rounded-xl border border-secondary-container">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-secondary">check_circle</span>
                            <span class="font-label-md font-bold text-on-secondary-container uppercase tracking-wide">Ready to Record</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-on-secondary-container font-bold uppercase">Amount to Defer</p>
                            <p class="font-numeric-pos text-secondary text-headline-sm">{{ Number(total).toFixed(2) }} EGP</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-lg bg-surface-container-low border-t border-outline-variant flex gap-md">
                <button
                    class="flex-1 py-lg border-2 border-outline text-outline font-bold rounded-xl hover:bg-surface-container-high transition-colors active:scale-95 transition-transform"
                    @click="handleClose"
                >
                    Cancel Transaction
                </button>
                <button
                    class="flex-[2] py-lg bg-primary text-on-primary font-bold text-headline-sm rounded-xl flex items-center justify-center gap-md shadow-lg shadow-primary/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!canConfirm"
                    @click="handleConfirm"
                >
                    {{ activeTab === DEFFERED_METHOD_ID ? 'Record Debt' : 'Complete Payment' }}
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
