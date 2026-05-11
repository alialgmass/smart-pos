<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
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
    form.post(route('customers.debt-payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const debtStatusBadge = (status: number) => {
    switch (status) {
        case 1: return { variant: 'warning' as const, label: 'Open' };
        case 2: return { variant: 'default' as const, label: 'Partial' };
        case 3: return { variant: 'success' as const, label: 'Paid' };
        case 4: return { variant: 'secondary' as const, label: 'Written Off' };
        default: return { variant: 'outline' as const, label: 'Unknown' };
    }
};

const paymentMethodLabel = (method: number) => {
    switch (method) {
        case 1: return 'Cash';
        case 2: return 'Card';
        case 3: return 'Mixed';
        case 4: return 'Deferred';
        default: return 'Unknown';
    }
};
</script>

<template>
    <Head :title="customer.name" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center gap-4">
            <Link :href="route('customers.index')">
                <Button variant="outline">&larr; Back</Button>
            </Link>
            <h1 class="text-2xl font-bold">{{ customer.name }}</h1>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Phone</p>
                <p class="text-lg font-medium">{{ customer.phone }}</p>
            </div>
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Debt Balance</p>
                <p class="text-lg font-medium">${{ customer.debt_balance }}</p>
            </div>
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Loyalty Points</p>
                <p class="text-lg font-medium">{{ customer.loyalty_points }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold">Debts</h2>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Amount</TableHead>
                            <TableHead>Paid</TableHead>
                            <TableHead>Balance</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Due Date</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="debt in customer.debts" :key="debt.id">
                            <TableCell>${{ debt.amount }}</TableCell>
                            <TableCell>${{ debt.paid_amount }}</TableCell>
                            <TableCell>${{ (parseFloat(debt.amount) - parseFloat(debt.paid_amount)).toFixed(2) }}</TableCell>
                            <TableCell>
                                <Badge :variant="debtStatusBadge(debt.status).variant">
                                    {{ debtStatusBadge(debt.status).label }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ debt.due_date ?? '-' }}</TableCell>
                            <TableCell class="text-right">
                                <Dialog v-if="debt.status !== 3 && debt.status !== 4">
                                    <DialogTrigger as-child>
                                        <Button variant="outline" size="sm" @click="payDebt(debt.id)">Pay</Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>Record Payment</DialogTitle>
                                            <DialogDescription>
                                                Remaining balance: ${{ (parseFloat(debt.amount) - parseFloat(debt.paid_amount)).toFixed(2) }}
                                            </DialogDescription>
                                        </DialogHeader>

                                        <form @submit.prevent="submitPayment" class="grid gap-4">
                                            <div class="grid gap-2">
                                                <Label for="amount">Amount</Label>
                                                <Input id="amount" type="number" step="0.01" v-model="form.amount" required />
                                            </div>
                                            <div class="grid gap-2">
                                                <Label for="payment_method">Payment Method</Label>
                                                <Select v-model="form.payment_method">
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Select method" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="1">Cash</SelectItem>
                                                        <SelectItem value="2">Card</SelectItem>
                                                        <SelectItem value="3">Mixed</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <DialogFooter>
                                                <Button type="submit" :disabled="form.processing">
                                                    <Spinner v-if="form.processing" />
                                                    Record Payment
                                                </Button>
                                            </DialogFooter>
                                        </form>
                                    </DialogContent>
                                </Dialog>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="customer.debts.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground">
                                No debts found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-for="debt in customer.debts" :key="'payments-' + debt.id">
                <div v-if="debt.payments.length > 0" class="ml-4 mt-2">
                    <h3 class="text-sm font-medium text-muted-foreground mb-1">Payments for Debt #{{ debt.id }}</h3>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Amount</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in debt.payments" :key="payment.id">
                                <TableCell>${{ payment.amount }}</TableCell>
                                <TableCell>{{ paymentMethodLabel(payment.payment_method) }}</TableCell>
                                <TableCell>{{ payment.created_at }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold">Loyalty History</h2>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Points</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Reference</TableHead>
                            <TableHead>Date</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="txn in customer.loyalty_transactions" :key="txn.id">
                            <TableCell :class="txn.points > 0 ? 'text-green-600' : 'text-red-600'">
                                {{ txn.points > 0 ? '+' : '' }}{{ txn.points }}
                            </TableCell>
                            <TableCell>{{ txn.type === 1 ? 'Earned' : 'Redeemed' }}</TableCell>
                            <TableCell>{{ txn.reference ?? '-' }}</TableCell>
                            <TableCell>{{ txn.created_at }}</TableCell>
                        </TableRow>
                        <TableRow v-if="customer.loyalty_transactions.length === 0">
                            <TableCell colspan="4" class="text-center text-muted-foreground">
                                No loyalty transactions yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
