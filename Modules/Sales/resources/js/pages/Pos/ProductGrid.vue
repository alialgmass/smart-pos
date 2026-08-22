<script setup lang="ts">
import {
    categories as categoriesRoute,
    search as searchProductsRoute,
} from '@/actions/Modules/Sales/Http/Controllers/PosController';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search, LoaderCircle } from 'lucide-vue-next';
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCartStore } from '../../stores/useCartStore';

interface PosProduct {
    id: number;
    name: string;
    barcode: string | null;
    price: number;
    cost: number;
    stock_qty: number;
    has_variants?: boolean;
    category_id: number | null;
}

const { t } = useI18n();
const cart = useCartStore();
const search = ref('');
const selectedCategory = ref<number | null>(null);
const isLoading = ref(false);

const products = ref<PosProduct[]>([]);
const categories = ref<Array<{ id: number; name: string }>>([]);

async function fetchCategories() {
    const response = await fetch(categoriesRoute.url());

    if (!response.ok) {
return;
}

    categories.value = await response.json();
}

async function fetchProducts() {
    isLoading.value = true;

    try {
        const response = await fetch(
            searchProductsRoute.url({
                query: {
                    q: search.value || undefined,
                    category_id: selectedCategory.value ?? undefined,
                },
            }),
        );

        if (!response.ok) {
return;
}

        const data = (await response.json()) as Array<
            Omit<PosProduct, 'price' | 'cost' | 'stock_qty'> & {
                price: string | number;
                cost: string | number;
                stock_qty: string | number;
            }
        >;
        products.value = data.map((product) => ({
            ...product,
            price: Number(product.price),
            cost: Number(product.cost),
            stock_qty: Number(product.stock_qty),
        }));
    } finally {
        isLoading.value = false;
    }
}

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchProducts, 300);
});

watch(selectedCategory, () => {
    fetchProducts();
});

function addToCart(product: PosProduct) {
    cart.addItem(product);
}
onMounted(() => {
    fetchCategories();
    fetchProducts();
});
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="border-b bg-card p-3">
            <div class="relative">
                <Search
                    class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    type="text"
                    :placeholder="t('sales::pos.search_products')"
                    class="ps-9"
                    autocomplete="off"
                />
            </div>
        </div>

        <div class="flex gap-1 overflow-x-auto border-b bg-muted/50 p-2">
            <button
                :class="[
                    'rounded-full px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                    selectedCategory === null
                        ? 'bg-primary text-primary-foreground'
                        : 'border border-border bg-card text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                ]"
                @click="selectedCategory = null"
            >
                {{ t('sales::pos.all') }}
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                :class="[
                    'rounded-full px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                    selectedCategory === cat.id
                        ? 'bg-primary text-primary-foreground'
                        : 'border border-border bg-card text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                ]"
                @click="selectedCategory = cat.id"
            >
                {{ cat.name }}
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
                <button
                    v-for="product in products"
                    :key="product.id"
                    class="group cursor-pointer rounded-lg border border-border bg-card p-2 text-center transition-all hover:border-ring hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
                    :disabled="product.stock_qty <= 0"
                    @click="addToCart(product)"
                >
                    <div
                        class="mb-2 flex aspect-square w-full items-center justify-center rounded-md bg-muted text-lg font-semibold text-muted-foreground transition-colors group-hover:bg-accent"
                    >
                        {{ product.name.charAt(0) }}
                    </div>
                    <p class="truncate text-xs font-medium text-card-foreground">
                        {{ product.name }}
                    </p>
                    <p class="mt-0.5 text-sm font-bold tabular-nums text-primary">
                        {{ Number(product.price).toFixed(2) }}
                    </p>
                    <Badge
                        v-if="product.stock_qty <= 0"
                        variant="destructive"
                        class="mt-1 text-[10px]"
                    >
                        {{ t('sales::pos.out_of_stock') }}
                    </Badge>
                </button>
            </div>

            <div
                v-if="isLoading"
                class="flex h-full flex-col items-center justify-center gap-2 text-muted-foreground"
            >
                <LoaderCircle class="size-6 animate-spin" />
                <p class="text-sm">{{ t('sales::pos.loading_products') }}</p>
            </div>
            <div
                v-else-if="products.length === 0"
                class="flex h-full items-center justify-center text-muted-foreground"
            >
                <p class="text-sm">{{ t('sales::pos.empty_grid') }}</p>
            </div>
        </div>
    </div>
</template>
