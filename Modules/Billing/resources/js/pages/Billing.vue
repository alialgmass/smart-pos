<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { pricing } from '@/routes/billing';

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

const statusLabels: Record<number, string> = {
    1: 'Trialing',
    2: 'Active',
    3: 'Past Due',
    4: 'Grace',
    5: 'Read Only',
    6: 'Cancelled',
};
</script>

<template>
    <Head title="Billing" />

    <div class="mx-auto max-w-4xl space-y-8 p-6">
        <div>
            <h1 class="text-2xl font-bold">Billing</h1>
            <p class="text-muted-foreground">Manage your subscription and billing details</p>
        </div>

        <div v-if="!subscription && trial_ends_at" class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">Trial period</h2>
            <p class="mt-2 text-muted-foreground">
                Your trial ends on {{ new Date(trial_ends_at).toLocaleDateString() }}.
                Choose a plan to continue using all features.
            </p>
            <Link
                :href="pricing()"
                class="mt-4 inline-block rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            >
                View plans
            </Link>
        </div>

        <div v-if="subscription" class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">Current plan</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">Plan</dt>
                    <dd class="font-medium">{{ subscription.plan_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Status</dt>
                    <dd class="font-medium">{{ statusLabels[subscription.status] ?? 'Unknown' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Start date</dt>
                    <dd class="font-medium">{{ new Date(subscription.starts_at).toLocaleDateString() }}</dd>
                </div>
                <div v-if="subscription.ends_at">
                    <dt class="text-sm text-muted-foreground">Renewal date</dt>
                    <dd class="font-medium">{{ new Date(subscription.ends_at).toLocaleDateString() }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <h2 class="text-lg font-semibold">Available plans</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="rounded-md border p-4"
                >
                    <h3 class="font-semibold">{{ plan.name }}</h3>
                    <p class="mt-1 text-2xl font-bold">${{ plan.price_monthly }}</p>
                    <p class="text-sm text-muted-foreground">per month</p>
                    <ul class="mt-3 space-y-1 text-sm">
                        <li>{{ plan.max_users }} staff accounts</li>
                        <li>{{ plan.max_products }} products</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
