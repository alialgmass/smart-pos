<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    product: {
        id: number;
        name: string;
    };
}>();
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Edit: {{ product.name }}</h1>
        <p class="text-muted-foreground">Product editing form coming soon.</p>
        <Button variant="outline" @click="$inertia.visit(route('inventory.products.index'))">
            Back to Products
        </Button>
    </div>
</template>
