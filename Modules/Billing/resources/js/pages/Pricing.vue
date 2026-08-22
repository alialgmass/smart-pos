<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    plans: Array<{
        id: number;
        name: string;
        price_monthly: number;
        max_users: number;
        max_products: number;
        features: string[] | null;
    }>;
}>();
</script>

<template>
    <Head :title="t('billing::pricing.title')" />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight">{{ t('billing::pricing.title') }}</h1>
            <p class="mt-4 text-lg text-muted-foreground">
                {{ t('billing::pricing.subtitle') }}
            </p>
        </div>

        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="plan in plans"
                :key="plan.id"
                class="flex flex-col rounded-xl border bg-card p-8 shadow-sm"
            >
                <h2 class="text-xl font-semibold">{{ plan.name }}</h2>
                <p class="mt-4">
                    <span class="text-4xl font-bold">${{ plan.price_monthly }}</span>
                    <span class="text-muted-foreground">{{ t('billing::pricing.month') }}</span>
                </p>

                <ul class="mt-8 flex-1 space-y-3">
                    <li class="flex items-center gap-2">
                        <span class="text-green-500">&#10003;</span>
                        {{ t('billing::pricing.staff_accounts', { n: plan.max_users }) }}
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-green-500">&#10003;</span>
                        {{ t('billing::pricing.up_to_products', { n: plan.max_products }) }}
                    </li>
                    <li
                        v-for="feature in (plan.features ?? [])"
                        :key="feature"
                        class="flex items-center gap-2"
                    >
                        <span class="text-green-500">&#10003;</span>
                        {{ feature }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
