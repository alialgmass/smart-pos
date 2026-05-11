<script setup lang="ts">
import { useCartStore } from '../../stores/useCartStore'
import { ref } from 'vue'

const emit = defineEmits<{
  checkout: []
}>()

const cart = useCartStore()
const discountInput = ref('')
const discountError = ref('')

function validateDiscount() {
  discountError.value = ''
  const amount = parseFloat(discountInput.value)
  if (isNaN(amount) || amount <= 0) {
    discountError.value = ''
    return
  }
  const maxDiscount = cart.subtotal.value * 0.5
  if (amount > maxDiscount) {
    discountError.value = `Max discount is ${maxDiscount.toFixed(2)}`
  }
}

function handleCheckout() {
  if (cart.itemCount.value === 0) return
  emit('checkout')
}
</script>

<template>
  <div class="flex flex-col h-full">
    <div class="p-3 border-b border-gray-200 bg-gray-50">
      <h2 class="text-lg font-semibold text-gray-800">Cart ({{ cart.itemCount.value }})</h2>
    </div>

    <div class="flex-1 overflow-y-auto p-3 space-y-2">
      <div
        v-for="(item, index) in cart.items.value"
        :key="index"
        class="flex items-center gap-2 bg-gray-50 rounded-lg p-2"
      >
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">{{ item.name }}</p>
          <p class="text-xs text-gray-500">{{ Number(item.price).toFixed(2) }} x {{ item.qty }}</p>
        </div>
        <div class="flex items-center gap-1">
          <button
            class="w-6 h-6 rounded bg-gray-200 text-gray-600 text-xs flex items-center justify-center hover:bg-gray-300"
            @click="cart.updateQty(index, item.qty - 1)"
          >
            -
          </button>
          <input
            :value="item.qty"
            type="number"
            min="1"
            :max="item.maxQty"
            class="w-12 text-center text-sm border border-gray-300 rounded px-1 py-0.5"
            @input="(e) => cart.updateQty(index, parseInt((e.target as HTMLInputElement).value) || 0)"
          />
          <button
            class="w-6 h-6 rounded bg-gray-200 text-gray-600 text-xs flex items-center justify-center hover:bg-gray-300"
            :disabled="item.qty >= item.maxQty"
            @click="cart.updateQty(index, item.qty + 1)"
          >
            +
          </button>
        </div>
        <p class="text-sm font-semibold text-gray-800 w-16 text-right">
          {{ Number(item.total).toFixed(2) }}
        </p>
        <button
          class="text-red-400 hover:text-red-600 text-lg leading-none"
          @click="cart.removeItem(index)"
        >
          &times;
        </button>
      </div>

      <div v-if="cart.items.value.length === 0" class="flex items-center justify-center h-full text-gray-400">
        <p>Cart is empty</p>
      </div>
    </div>

    <div class="p-3 border-t border-gray-200 space-y-2 bg-gray-50">
      <div class="space-y-1">
        <div class="flex justify-between text-sm text-gray-600">
          <span>Subtotal</span>
          <span>{{ Number(cart.subtotal.value).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
          <span>Tax</span>
          <span>{{ Number(cart.taxAmount.value).toFixed(2) }}</span>
        </div>
        <div v-if="cart.discountAmount.value > 0" class="flex justify-between text-sm text-green-600">
          <span>Discount</span>
          <span>-{{ Number(cart.discountAmount.value).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold text-gray-800 pt-1 border-t border-gray-300">
          <span>Total</span>
          <span>{{ Number(cart.total.value).toFixed(2) }}</span>
        </div>
      </div>

      <div>
        <input
          v-model="discountInput"
          type="number"
          step="0.01"
          min="0"
          placeholder="Discount amount"
          class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          @input="validateDiscount"
        />
        <p v-if="discountError" class="text-xs text-red-500 mt-1">{{ discountError }}</p>
      </div>

      <button
        class="w-full py-2.5 rounded-lg text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        :class="cart.itemCount.value > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400'"
        :disabled="cart.itemCount.value === 0"
        @click="handleCheckout"
      >
        Checkout
      </button>
    </div>
  </div>
</template>
