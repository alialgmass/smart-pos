<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { dashboard, login, register } from '@/routes'

withDefaults(
    defineProps<{
        canRegister: boolean
    }>(),
    {
        canRegister: true,
    },
)

const page = usePage()
const dir = computed(() => (page.props.locale === 'ar' ? 'rtl' : 'ltr'))

const features = [
    {
        title: 'Fast & Efficient POS',
        description: 'Process transactions in seconds with an optimized interface designed for speed.',
        icon: '⚡',
    },
    {
        title: 'Inventory Management',
        description: 'Track stock levels, manage variants, and get low-stock alerts in real time.',
        icon: '📦',
    },
    {
        title: 'Customer Management',
        description: 'Build customer profiles, track purchase history, and manage loyalty points.',
        icon: '👥',
    },
    {
        title: 'Sales Analytics',
        description: 'Understand your business with detailed reports and visual insights.',
        icon: '📊',
    },
    {
        title: 'Multi-currency Support',
        description: 'Handle multiple currencies and payment methods seamlessly.',
        icon: '💱',
    },
    {
        title: 'Cloud-based Access',
        description: 'Access your business from anywhere, on any device, at any time.',
        icon: '☁️',
    },
]

const plans = [
    {
        name: 'Starter',
        price: '$29',
        period: '/month',
        features: ['Up to 500 transactions/mo', 'Basic reports', 'Single user', 'Email support'],
    },
    {
        name: 'Business',
        price: '$79',
        period: '/month',
        popular: true,
        features: ['Unlimited transactions', 'Advanced analytics', 'Up to 5 users', 'Priority support', 'Inventory management'],
    },
    {
        name: 'Enterprise',
        price: '$199',
        period: '/month',
        features: ['Everything in Business', 'Unlimited users', 'API access', 'Dedicated support', 'Custom integrations'],
    },
]
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    </Head>

    <div class="flex min-h-screen flex-col bg-white dark:bg-[#0a0a0a]" :dir="dir">
        <!-- Navbar -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-md dark:border-gray-800 dark:bg-[#0a0a0a]/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="dashboard()" class="flex items-center gap-2 font-bold text-xl">
                    <span class="text-blue-600">POS</span>
                    <span class="text-gray-900 dark:text-white">System</span>
                </Link>

                <nav class="flex items-center gap-4">
                    <Link
                        v-if="page.props.auth?.user"
                        :href="dashboard()"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden px-4 py-20 sm:px-6 sm:py-32 lg:px-8">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45%_40%_at_50%_60%,rgba(59,130,246,0.12),transparent)] dark:bg-[radial-gradient(45%_40%_at_50%_60%,rgba(59,130,246,0.08),transparent)]" />
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl dark:text-white">
                    Modern Point of Sale
                    <span class="text-blue-600">for Your Business</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                    A powerful, cloud-based POS system that helps you manage sales, inventory, customers, and staff — all from one intuitive dashboard.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-lg bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors"
                    >
                        Get Started Free
                    </Link>
                    <Link
                        :href="login()"
                        class="rounded-lg border border-gray-300 px-8 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900 transition-colors"
                    >
                        Sign In
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="border-t border-gray-200 px-4 py-20 sm:px-6 lg:px-8 dark:border-gray-800">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Everything you need to run your business
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Comprehensive tools designed for modern retail and hospitality.
                    </p>
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-xl border border-gray-200 p-6 transition-all hover:border-blue-200 hover:shadow-md dark:border-gray-800 dark:hover:border-blue-900"
                    >
                        <div class="mb-4 text-3xl">{{ feature.icon }}</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ feature.title }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section class="border-t border-gray-200 px-4 py-20 sm:px-6 lg:px-8 dark:border-gray-800">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Simple, transparent pricing
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Choose the plan that fits your business. No hidden fees.
                    </p>
                </div>
                <div class="mt-16 grid gap-8 lg:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.name"
                        :class="[
                            'relative rounded-xl border p-8 transition-all',
                            plan.popular
                                ? 'border-blue-600 shadow-lg shadow-blue-600/10 scale-105'
                                : 'border-gray-200 dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-900',
                        ]"
                    >
                        <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-blue-600 px-4 py-1 text-xs font-semibold text-white">
                            Most Popular
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ plan.name }}</h3>
                        <p class="mt-4">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">{{ plan.price }}</span>
                            <span class="text-sm text-gray-500">{{ plan.period }}</span>
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li
                                v-for="feat in plan.features"
                                :key="feat"
                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <svg class="h-4 w-4 flex-shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ feat }}
                            </li>
                        </ul>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            :class="[
                                'mt-8 block w-full rounded-lg py-2.5 text-center text-sm font-semibold transition-colors',
                                plan.popular
                                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                                    : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900',
                            ]"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-blue-600 px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Ready to transform your business?
                </h2>
                <p class="mt-4 text-lg text-blue-100">
                    Join thousands of businesses already using POS System to streamline their operations.
                </p>
                <div class="mt-10">
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center rounded-lg bg-white px-8 py-3 text-sm font-semibold text-blue-600 shadow-sm hover:bg-blue-50 transition-colors"
                    >
                        Start Free Trial
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 px-4 py-8 dark:border-gray-800">
            <div class="mx-auto flex max-w-7xl items-center justify-between text-sm text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} POS System. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <Link :href="login()" class="hover:text-gray-700 dark:hover:text-gray-300">Log in</Link>
                    <Link v-if="canRegister" :href="register()" class="hover:text-gray-700 dark:hover:text-gray-300">Register</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
