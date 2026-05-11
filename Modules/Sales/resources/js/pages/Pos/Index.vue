<script setup lang="ts">
import { useCartStore } from '../../stores/useCartStore'
import { useBarcodeScanner } from '../../composables/useBarcodeScanner'
import CartSidebar from './CartSidebar.vue'
import ProductGrid from './ProductGrid.vue'
import PaymentModal from './PaymentModal.vue'
import { ref } from 'vue'

const cart = useCartStore()
const showPaymentModal = ref(false)

function onBarcodeScanned(barcode: string) {
  // TODO: search product by barcode and add to cart
  console.log('Barcode scanned:', barcode)
}

function onCheckout() {
  if (cart.itemCount.value === 0) return
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
  <div class="flex h-screen overflow-hidden bg-gray-100">
    <div class="flex-1 flex flex-col overflow-hidden">
      <ProductGrid />
    </div>
    <div class="w-96 flex-shrink-0 border-l border-gray-300 bg-white">
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
