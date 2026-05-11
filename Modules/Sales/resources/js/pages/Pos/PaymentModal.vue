<script setup lang="ts">
import { ref, computed, watch } from 'vue'
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
        customerId?: number
        customerName?: string
    }]
    close: []
}>()

const paymentMethod = ref(1)
const paidAmount = ref(0)
const calculatedChange = computed(() => Math.max(0, paidAmount.value - props.total))

// Deferred payment state
const customerQuery = ref('')
const customerResults = ref<Customer[]>([])
const selectedCustomer = ref<Customer | null>(null)
const searching = ref(false)
const showResults = ref(false)

const DEFFERED_METHOD_ID = 4

watch(customerQuery, async (query) => {
    if (paymentMethod.value !== DEFFERED_METHOD_ID || !query || query.length < 1) {
        customerResults.value = []
        showResults.value = false
        return
    }

    searching.value = true
    showResults.value = true

    try {
        const res = await axios.get(route('customers.search'), {
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
    emit('complete', {
        paymentMethod: paymentMethod.value,
        paidAmount: paidAmount.value,
        changeAmount: calculatedChange.value,
        customerId: paymentMethod.value === DEFFERED_METHOD_ID ? selectedCustomer.value?.id : undefined,
        customerName: paymentMethod.value === DEFFERED_METHOD_ID ? selectedCustomer.value?.name : undefined,
    })
}

function handleClose() {
    emit('close')
}

const canConfirm = computed(() => {
    if (paymentMethod.value === DEFFERED_METHOD_ID) {
        return !!selectedCustomer.value
    }
    return paidAmount.value > 0 && paidAmount.value >= props.total
})

const methods = [
    { id: 1, label: 'Cash' },
    { id: 2, label: 'Card' },
    { id: 3, label: 'Mixed' },
    { id: 4, label: 'Deferred' },
]
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Payment</h2>
                <button class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="handleClose">&times;</button>
            </div>

            <div class="text-center mb-4">
                <p class="text-sm text-gray-500">Total Amount</p>
                <p class="text-3xl font-bold text-gray-800">{{ Number(total).toFixed(2) }}</p>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Payment Method</p>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="method in methods"
                        :key="method.id"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium border transition-colors',
                            paymentMethod === method.id
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                        ]"
                        @click="paymentMethod = method.id"
                    >
                        {{ method.label }}
                    </button>
                </div>
            </div>

            <!-- Standard payment input (hidden for deferred) -->
            <div v-if="paymentMethod !== DEFFERED_METHOD_ID" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid</label>
                <input
                    v-model.number="paidAmount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter amount"
                />
                <p v-if="paidAmount > 0 && paidAmount < total" class="text-xs text-orange-500 mt-1">
                    Insufficient amount
                </p>
                <p v-if="calculatedChange > 0" class="text-sm text-green-600 mt-1">
                    Change: {{ calculatedChange.toFixed(2) }}
                </p>
            </div>

            <!-- Deferred payment: customer selection -->
            <div v-if="paymentMethod === DEFFERED_METHOD_ID" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Customer <span class="text-red-500">*</span>
                </label>

                <div v-if="!selectedCustomer" class="relative">
                    <input
                        v-model="customerQuery"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search by name or phone..."
                        autocomplete="off"
                    />

                    <div
                        v-if="showResults && customerQuery.length > 0"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto z-10"
                    >
                        <div v-if="searching" class="px-4 py-3 text-sm text-gray-500 text-center">
                            Searching...
                        </div>
                        <div
                            v-else-if="customerResults.length === 0"
                            class="px-4 py-3 text-sm text-gray-500 text-center"
                        >
                            No customers found
                        </div>
                        <button
                            v-for="customer in customerResults"
                            :key="customer.id"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center justify-between"
                            @click="selectCustomer(customer)"
                        >
                            <span class="font-medium">{{ customer.name }}</span>
                            <span class="text-gray-400">{{ customer.phone }}</span>
                        </button>
                    </div>
                </div>

                <div v-else class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ selectedCustomer.name }}</p>
                        <p class="text-xs text-gray-500">{{ selectedCustomer.phone }}</p>
                    </div>
                    <button
                        class="text-gray-400 hover:text-gray-600 text-sm"
                        @click="clearCustomer"
                    >
                        Change
                    </button>
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    Deferred payment will be recorded as a debt for this customer.
                </p>
            </div>

            <div class="flex gap-2">
                <button
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium"
                    @click="handleClose"
                >
                    Cancel
                </button>
                <button
                    class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!canConfirm"
                    @click="handleConfirm"
                >
                    {{ paymentMethod === DEFFERED_METHOD_ID ? 'Record Debt' : 'Confirm Payment' }}
                </button>
            </div>
        </div>
    </div>
</template>
