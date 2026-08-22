<script setup lang="ts">
import { Minus, Plus, X } from 'lucide-vue-next'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '../../stores/useCartStore'

const { t } = useI18n()

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
    return
  }

  const maxDiscount = cart.subtotal.value * 0.5

  if (amount > maxDiscount) {
    discountError.value = t('sales::pos.max_discount_is', {
      amount: maxDiscount.toFixed(2),
    })
  }
}

function handleCheckout() {
  if (cart.itemCount.value === 0) {
return
}

  emit('checkout')
}
</script>

<template>
  <div class="flex h-full flex-col">
    <div class="border-b bg-card p-3">
      <h2 class="text-lg font-semibold text-card-foreground">
        {{ t('sales::pos.cart', { count: cart.itemCount.value }) }}
      </h2>
    </div>

    <div class="flex-1 space-y-2 overflow-y-auto p-3">
      <div
        v-for="(item, index) in cart.items.value"
        :key="index"
        class="flex items-center gap-2 rounded-lg border border-border bg-muted/50 p-2"
      >
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-medium text-card-foreground">{{ item.name }}</p>
          <p class="text-xs text-muted-foreground tabular-nums">
            {{ Number(item.price).toFixed(2) }} × {{ item.qty }}
          </p>
        </div>
        <div class="flex items-center gap-1">
          <button
            class="flex size-6 items-center justify-center rounded bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
            @click="cart.updateQty(index, item.qty - 1)"
          >
            <Minus class="size-3" />
          </button>
          <input
            :value="item.qty"
            type="number"
            min="1"
            :max="item.maxQty"
            class="w-12 rounded-md border border-input bg-background px-1 py-0.5 text-center text-sm tabular-nums outline-none focus:ring-2 focus:ring-ring"
            @input="(e) => cart.updateQty(index, parseInt((e.target as HTMLInputElement).value) || 0)"
          />
          <button
            class="flex size-6 items-center justify-center rounded bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50"
            :disabled="item.qty >= item.maxQty"
            @click="cart.updateQty(index, item.qty + 1)"
          >
            <Plus class="size-3" />
          </button>
        </div>
        <p class="w-16 text-end text-sm font-semibold tabular-nums text-card-foreground">
          {{ Number(item.total).toFixed(2) }}
        </p>
        <button
          class="text-muted-foreground transition-colors hover:text-destructive"
          @click="cart.removeItem(index)"
        >
          <X class="size-4" />
        </button>
      </div>

      <div
        v-if="cart.items.value.length === 0"
        class="flex h-full items-center justify-center text-muted-foreground"
      >
        <p class="text-sm">{{ t('sales::pos.cart_empty') }}</p>
      </div>
    </div>

    <div class="space-y-2 border-t bg-card p-3">
      <div class="space-y-1">
        <div class="flex justify-between text-sm text-muted-foreground">
          <span>{{ t('sales::pos.subtotal') }}</span>
          <span class="tabular-nums">{{ Number(cart.subtotal.value).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-muted-foreground">
          <span>{{ t('sales::pos.tax') }}</span>
          <span class="tabular-nums">{{ Number(cart.taxAmount.value).toFixed(2) }}</span>
        </div>
        <div v-if="cart.discountAmount.value > 0" class="flex justify-between text-sm font-medium text-emerald-600 dark:text-emerald-400">
          <span>{{ t('sales::pos.discount') }}</span>
          <span class="tabular-nums">-{{ Number(cart.discountAmount.value).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between border-t pt-1 text-lg font-bold text-card-foreground">
          <span>{{ t('sales::pos.total') }}</span>
          <span class="tabular-nums">{{ Number(cart.total.value).toFixed(2) }}</span>
        </div>
      </div>

      <div>
        <input
          v-model="discountInput"
          type="number"
          step="0.01"
          min="0"
          :placeholder="t('sales::pos.discount_placeholder')"
          class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm tabular-nums outline-none transition-colors focus:border-ring focus:ring-2 focus:ring-ring/30"
          @input="validateDiscount"
        />
        <p v-if="discountError" class="mt-1 text-xs text-destructive">{{ discountError }}</p>
      </div>

      <button
        class="w-full rounded-lg py-2.5 text-sm font-semibold text-primary-foreground transition-all active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50"
        :class="cart.itemCount.value > 0 ? 'bg-primary hover:bg-primary/90' : 'bg-muted-foreground/50'"
        :disabled="cart.itemCount.value === 0"
        @click="handleCheckout"
      >
        {{ t('sales::pos.checkout') }}
      </button>
    </div>
  </div>
</template>
