<script setup lang="ts">
import { useCartStore } from '../../stores/useCartStore'
import { ref } from 'vue'

const cart = useCartStore()
const search = ref('')
const selectedCategory = ref<number | null>(null)

const products = ref<
  Array<{
    id: number
    name: string
    barcode: string | null
    price: number
    cost: number
    stock_qty: number
    category_id: number | null
  }>
>([])

const categories = ref<Array<{ id: number; name: string }>>([])

// TODO: fetch products and categories from API

function addToCart(product: (typeof products.value)[0]) {
  cart.addItem(product)
}
</script>

<template>
  <div class="flex flex-col h-full">
    <div class="p-3 bg-white border-b border-gray-200">
      <input
        v-model="search"
        type="text"
        placeholder="Search products..."
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <div class="flex gap-1 p-2 bg-gray-50 overflow-x-auto border-b border-gray-200">
      <button
        :class="[
          'px-3 py-1.5 text-sm rounded-full whitespace-nowrap',
          selectedCategory === null
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100',
        ]"
        @click="selectedCategory = null"
      >
        All
      </button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        :class="[
          'px-3 py-1.5 text-sm rounded-full whitespace-nowrap',
          selectedCategory === cat.id
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100',
        ]"
        @click="selectedCategory = cat.id"
      >
        {{ cat.name }}
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-3">
      <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
        <button
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-lg border border-gray-200 p-2 text-center hover:shadow-md transition-shadow cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="product.stock_qty <= 0"
          @click="addToCart(product)"
        >
          <div class="w-full aspect-square bg-gray-100 rounded-md mb-1 flex items-center justify-center text-gray-400 text-xs">
            {{ product.name.charAt(0) }}
          </div>
          <p class="text-xs font-medium text-gray-800 truncate">{{ product.name }}</p>
          <p class="text-sm font-bold text-blue-600">{{ Number(product.price).toFixed(2) }}</p>
          <p v-if="product.stock_qty <= 0" class="text-xs text-red-500">Out of stock</p>
        </button>
      </div>

      <div v-if="products.length === 0" class="flex items-center justify-center h-full text-gray-400">
        <p>Search for products or scan barcode</p>
      </div>
    </div>
  </div>
</template>
