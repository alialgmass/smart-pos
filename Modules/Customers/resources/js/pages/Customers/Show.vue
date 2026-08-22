<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import PageHeader from '@/components/shared/PageHeader.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import { ArrowLeft, HandCoins, Star, ReceiptText } from 'lucide-vue-next';
import { index as indexRoute } from '@/routes/customers';
import { store as paymentStore } from '@/routes/customers/debt-payments';

interface DebtPayment {
    id: number;
    amount: string;
    payment_method: number;
    created_at: string;
    user?: { id: number; name: string };
}

interface CustomerDebt {
    id: number;
    amount: string;
    paid_amount: string;
    status: number;
    due_date: string | null;
    created_at: string;
    payments: DebtPayment[];
}

interface LoyaltyTransaction {
    id: number;
    points: number;
    type: number;
    reference: string | null;
    created_at: string;
}

interface Customer {
    id: number;
    name: string;
    phone: string;
    debt_balance: string;
    loyalty_points: number;
    debts: CustomerDebt[];
    loyalty_transactions: LoyaltyTransaction[];
}

const { t, te } = useI18n();

const props = defineProps<{
    customer: Customer;
}>();

const form = useForm({
    debt_id: 0,
    amount: '',
    payment_method: '1',
});

const payDebt = (debtId: number) => {
    form.debt_id = debtId;
    form.amount = '';
    form.payment_method = '1';
};

const submitPayment = () => {
    form.post(paymentStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const methodKeys = ['payment_cash', 'payment_card', 'payment_mixed', 'payment_deferred'];

const paymentMethodLabel = (method: number): string => {
    const key = `customers::debts.${methodKeys[method - 1]}`;
    return te(key) ? t(key) : t('customers::debts.unknown');
};

const debtStatusBadge = (status: number) => {
    switch (status) {
        case 1: return { variant: 'warning' as const, label: t('customers::debts.open') };
        case 2: return { variant: 'default' as const, label: t('customers::debts.partial') };
        case 3: return { variant: 'success' as const, label: t('customers::debts.paid') };
        case 4: return { variant: 'secondary' as const, label: t('customers::debts.written_off') };
        default: return { variant: 'outline' as const, label: t('customers::debts.unknown') };
    }
};
</script>

<template>
    <Head :title="customer.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-wrap items-center gap-4">
            <Link :href="indexRoute.url()">
                <Button variant="outline">
                    <ArrowLeft class="size-4 rtl:-scale-x-100" />
                    {{ t('customers::show.back') }}
                </Button>
            </Link>
            <PageHeader :title="customer.name" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-card p-4">
                <p class="text-sm text-muted-foreground">{{ t('customers::show.phone') }}</p>
                <p class="mt-1 text-lg font-medium tabular-nums" dir="ltr">{{ customer.phone }}</p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="text-sm text-muted-foreground">{{ t('customers::show.debt_balance') }}</p>
                <p class="mt-1 text-lg font-medium font-mono" :class="Number(customer.debt_balance) > 0 ? 'text-destructive' : ''">${{ Number(customer.debt_balance).toFixed(2) }}</p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="text-sm text-muted-foreground">{{ t('customers::show.loyalty_points') }}</p>
                <p class="mt-1 flex items-center gap-1.5 text-lg font-medium tabular-nums">
                    <Star class="size-4 text-amber-500" />
                    {{ customer.loyalty_points }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold">{{ t('customers::show.debts') }}</h2>
            <EmptyState
                v-if="customer.debts.length === 0"
                :icon="ReceiptText"
                :title="t('customers::show.no_debts')"
            />
            <div v-else class="overflow-hidden rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('customers::show.amount') }}</TableHead>
                            <TableHead>{{ t('customers::show.paid') }}</TableHead>
                            <TableHead>{{ t('customers::show.balance') }}</TableHead>
                            <TableHead>{{ t('customers::show.status') }}</TableHead>
                            <TableHead>{{ t('customers::show.due_date') }}</TableHead>
                            <TableHead class="text-end">{{ t('customers::show.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="debt in customer.debts" :key="debt.id">
                            <TableCell class="font-mono tabular-nums">${{ Number(debt.amount).toFixed(2) }}</TableCell>
                            <TableCell class="font-mono tabular-nums">${{ Number(debt.paid_amount).toFixed(2) }}</TableCell>
                            <TableCell class="font-mono tabular-nums">${{ (parseFloat(debt.amount) - parseFloat(debt.paid_amount)).toFixed(2) }}</TableCell>
                            <TableCell>
                                <Badge :variant="debtStatusBadge(debt.status).variant">
                                    {{ debtStatusBadge(debt.status).label }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ debt.due_date ?? t('customers::show.not_available') }}</TableCell>
                            <TableCell class="text-end">
                                <Dialog v-if="debt.status !== 3 && debt.status !== 4">
                                    <DialogTrigger as-child>
                                        <Button variant="outline" size="sm" @click="payDebt(debt.id)">
                                            <HandCoins class="size-3.5" />
                                            {{ t('customers::show.pay') }}
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>{{ t('customers::show.record_payment') }}</DialogTitle>
                                            <DialogDescription>
                                                {{ t('customers::show.remaining_balance', { amount: '$' + (parseFloat(debt.amount) - parseFloat(debt.paid_amount)).toFixed(2) }) }}
                                            </DialogDescription>
                                        </DialogHeader>

                                        <form @submit.prevent="submitPayment" class="grid gap-4">
                                            <div class="grid gap-2">
                                                <Label for="amount">{{ t('customers::show.amount_label') }}</Label>
                                                <Input id="amount" v-model="form.amount" type="number" step="0.01" required />
                                            </div>
                                            <div class="grid gap-2">
                                                <Label for="payment_method">{{ t('customers::show.payment_method') }}</Label>
                                                <Select v-model="form.payment_method">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue :placeholder="t('customers::show.select_method')" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="1">{{ t('customers::debts.payment_cash') }}</SelectItem>
                                                        <SelectItem value="2">{{ t('customers::debts.payment_card') }}</SelectItem>
                                                        <SelectItem value="3">{{ t('customers::debts.payment_mixed') }}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <DialogFooter>
                                                <Button type="submit" :disabled="form.processing">
                                                    {{ t('customers::show.record_payment') }}
                                                </Button>
                                            </DialogFooter>
                                        </form>
                                    </DialogContent>
                                </Dialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-for="debt in customer.debts" :key="'payments-' + debt.id">
                <div v-if="debt.payments.length > 0" class="ms-4 mt-2">
                    <h3 class="mb-1 text-sm font-medium text-muted-foreground">
                        {{ t('customers::show.payments_for_debt', { id: debt.id }) }}
                    </h3>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('customers::show.amount') }}</TableHead>
                                <TableHead>{{ t('customers::show.method') }}</TableHead>
                                <TableHead>{{ t('customers::show.date') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in debt.payments" :key="payment.id">
                                <TableCell class="font-mono tabular-nums">${{ Number(payment.amount).toFixed(2) }}</TableCell>
                                <TableCell>{{ paymentMethodLabel(payment.payment_method) }}</TableCell>
                                <TableCell>{{ payment.created_at }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold">{{ t('customers::show.loyalty_history') }}</h2>
            <EmptyState
                v-if="customer.loyalty_transactions.length === 0"
                :icon="Star"
                :title="t('customers::show.no_loyalty')"
            />
            <div v-else class="overflow-hidden rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('customers::show.points') }}</TableHead>
                            <TableHead>{{ t('customers::show.type') }}</TableHead>
                            <TableHead>{{ t('customers::show.reference') }}</TableHead>
                            <TableHead>{{ t('customers::show.date') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="txn in customer.loyalty_transactions" :key="txn.id">
                            <TableCell class="tabular-nums" :class="txn.points > 0 ? 'font-medium text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                {{ txn.points > 0 ? '+' : '' }}{{ txn.points }}
                            </TableCell>
                            <TableCell>{{ txn.type === 1 ? t('customers::show.earned') : t('customers::show.redeemed') }}</TableCell>
                            <TableCell>{{ txn.reference ?? t('customers::show.not_available') }}</TableCell>
                            <TableCell>{{ txn.created_at }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
