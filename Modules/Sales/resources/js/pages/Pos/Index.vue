<script setup lang="ts">
import { ref } from 'vue'
import { useBarcodeScanner } from '../../composables/useBarcodeScanner'
import { useCartStore } from '../../stores/useCartStore'
import CartSidebar from './CartSidebar.vue'
import PaymentModal from './PaymentModal.vue'
import ProductGrid from './ProductGrid.vue'

const cart = useCartStore()
const showPaymentModal = ref(false)

function onBarcodeScanned(barcode: string) {
  // TODO: search product by barcode and add to cart
  console.log('Barcode scanned:', barcode)
}

function onCheckout() {
  if (cart.itemCount.value === 0) {
return
}

  showPaymentModal.value = true
}

function onPaymentComplete(data: { paymentMethod: number; paidAmount: number; changeAmount: number }) {
  showPaymentModal.value = false
  // TODO: submit sale via API
  console.log('Payment complete:', data)
}

useBarcodeScanner(onBarcodeScanned)
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-background">
    <div class="flex flex-1 flex-col overflow-hidden">
      <ProductGrid />
    </div>
    <div class="w-96 shrink-0 border-s bg-card">
      <CartSidebar @checkout="onCheckout" />
    </div>
    <PaymentModal
      v-if="showPaymentModal"
      :total="cart.total.value"
      @complete="onPaymentComplete"
      @close="showPaymentModal = false"
    />
  </div>
</template>
