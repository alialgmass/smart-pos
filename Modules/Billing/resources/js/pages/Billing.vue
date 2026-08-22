<script setup lang="ts">
import { pricing } from '@/routes/billing';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, te } = useI18n();

defineProps<{
    subscription: {
        id: number;
        plan_id: number;
        plan_name: string;
        status: number;
        starts_at: string;
        ends_at: string | null;
        gateway: number | null;
    } | null;
    trial_ends_at: string | null;
    plans: Array<{
        id: number;
        name: string;
        price_monthly: number;
        max_users: number;
        max_products: number;
        features: string[] | null;
    }>;
}>();

const statusLabel = (status: number): string => {
    const keys: Record<number, string> = {
        1: 'status_trialing',
        2: 'status_active',
        3: 'status_past_due',
        4: 'status_grace',
        5: 'status_read_only',
        6: 'status_cancelled',
    };
    const key = `billing::billing.${keys[status] ?? 'status_unknown'}`;

    return te(key) ? t(key) : t('billing::billing.status_unknown');
};
</script>

<template>
    <Head :title="t('billing::billing.title')" />

    <div class="mx-auto max-w-4xl space-y-8 p-6">
        <div>
            <h1 class="text-2xl font-bold">{{ t('billing::billing.title') }}</h1>
            <p class="text-muted-foreground">{{ t('billing::billing.description') }}</p>
        </div>

        <div v-if="!subscription && trial_ends_at" class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">{{ t('billing::billing.trial_period') }}</h2>
            <p class="mt-2 text-muted-foreground">
                {{ t('billing::billing.trial_ends_on', { date: new Date(trial_ends_at).toLocaleDateString() }) }}
            </p>
            <Link
                :href="pricing()"
                class="mt-4 inline-block rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            >
                {{ t('billing::billing.view_plans') }}
            </Link>
        </div>

        <div v-if="subscription" class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">{{ t('billing::billing.current_plan') }}</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">{{ t('billing::billing.plan') }}</dt>
                    <dd class="font-medium">{{ subscription.plan_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ t('billing::billing.status') }}</dt>
                    <dd class="font-medium">{{ statusLabel(subscription.status) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ t('billing::billing.start_date') }}</dt>
                    <dd class="font-medium">{{ new Date(subscription.starts_at).toLocaleDateString() }}</dd>
                </div>
                <div v-if="subscription.ends_at">
                    <dt class="text-sm text-muted-foreground">{{ t('billing::billing.renewal_date') }}</dt>
                    <dd class="font-medium">{{ new Date(subscription.ends_at).toLocaleDateString() }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">{{ t('billing::billing.available_plans') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="rounded-md border p-4"
                >
                    <h3 class="font-semibold">{{ plan.name }}</h3>
                    <p class="mt-1 text-2xl font-bold">${{ plan.price_monthly }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('billing::billing.per_month') }}</p>
                    <ul class="mt-3 space-y-1 text-sm">
                        <li>{{ t('billing::billing.staff_accounts', { n: plan.max_users }) }}</li>
                        <li>{{ t('billing::billing.products_count', { n: plan.max_products }) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
