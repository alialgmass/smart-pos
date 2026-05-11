import { computed, ref } from 'vue'

export interface CartItem {
  productId?: number
  variantId?: number | null
  name: string
  price: number
  cost: number
  qty: number
  discount: number
  taxAmount: number
  total: number
  maxQty: number
}

const items = ref<CartItem[]>([])

export function useCartStore() {
  const subtotal = computed(() =>
    items.value.reduce((sum, item) => sum + item.price * item.qty, 0)
  )

  const discountAmount = computed(() =>
    items.value.reduce((sum, item) => sum + item.discount, 0)
  )

  const taxAmount = computed(() =>
    items.value.reduce((sum, item) => sum + item.taxAmount, 0)
  )

  const total = computed(() =>
    items.value.reduce((sum, item) => sum + item.total, 0)
  )

  const itemCount = computed(() =>
    items.value.reduce((sum, item) => sum + item.qty, 0)
  )

  function addItem(product: {
    id: number
    name: string
    price: number
    cost: number
    stock_qty: number
    has_variants?: boolean
    variant_id?: number | null
  }) {
    if (product.stock_qty <= 0) return

    const existing = items.value.find(
      (i) => i.productId === product.id && i.variantId === (product.variant_id ?? null)
    )

    if (existing) {
      if (existing.qty >= existing.maxQty) return
      existing.qty = Math.min(existing.qty + 1, existing.maxQty)
      existing.total =
        existing.price * existing.qty - existing.discount + existing.taxAmount
      return
    }

    items.value.push({
      productId: product.id,
      variantId: product.variant_id ?? null,
      name: product.name,
      price: product.price,
      cost: product.cost,
      qty: 1,
      discount: 0,
      taxAmount: 0,
      total: product.price,
      maxQty: product.stock_qty,
    })
  }

  function removeItem(index: number) {
    items.value.splice(index, 1)
  }

  function updateQty(index: number, qty: number) {
    if (qty <= 0) {
      removeItem(index)
      return
    }
    const item = items.value[index]
    if (!item) return
    item.qty = Math.min(qty, item.maxQty)
    item.total = item.price * item.qty - item.discount + item.taxAmount
  }

  function clearCart() {
    items.value = []
  }

  return {
    items,
    subtotal,
    discountAmount,
    taxAmount,
    total,
    itemCount,
    addItem,
    removeItem,
    updateQty,
    clearCart,
  }
}
