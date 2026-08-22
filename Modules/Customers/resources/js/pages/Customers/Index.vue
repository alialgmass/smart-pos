<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
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
import PageHeader from '@/components/shared/PageHeader.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import PaginationLinks from '@/components/shared/PaginationLinks.vue';
import { UserPlus, Users, ReceiptText } from 'lucide-vue-next';
import { index as indexRoute, store, show, debts } from '@/routes/customers';

interface Customer {
    id: number;
    name: string;
    phone: string;
    debt_balance: string;
    loyalty_points: number;
}

const { t } = useI18n();

const props = defineProps<{
    customers: {
        data: Customer[];
        current_page: number;
        last_page: number;
        total?: number;
        per_page?: number;
    };
}>();

const form = useForm({
    name: '',
    phone: '',
});

const createCustomer = () => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head :title="t('customers::customers.title')" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <PageHeader
                :title="t('customers::customers.title')"
                :description="t('customers::customers.subtitle')"
            />

            <div class="flex items-center gap-3">
                <Link :href="debts.url()">
                    <Button variant="outline">
                        <ReceiptText class="size-4" />
                        {{ t('customers::customers.manage_debts') }}
                    </Button>
                </Link>

                <Dialog>
                    <DialogTrigger as-child>
                        <Button>
                            <UserPlus class="size-4" />
                            {{ t('customers::customers.add_customer') }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{{ t('customers::customers.create_customer') }}</DialogTitle>
                            <DialogDescription>
                                {{ t('customers::customers.create_description') }}
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="createCustomer" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">{{ t('customers::customers.name') }}</Label>
                                <Input id="name" v-model="form.name" required />
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone">{{ t('customers::customers.phone') }}</Label>
                                <Input id="phone" v-model="form.phone" type="tel" dir="ltr" required />
                            </div>

                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">
                                    {{ t('customers::customers.save') }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <EmptyState
            v-if="props.customers.data.length === 0"
            :icon="Users"
            :title="t('customers::customers.title')"
        />

        <template v-else>
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('customers::customers.column_name') }}</TableHead>
                            <TableHead>{{ t('customers::customers.column_phone') }}</TableHead>
                            <TableHead>{{ t('customers::customers.column_debt_balance') }}</TableHead>
                            <TableHead>{{ t('customers::customers.column_loyalty_points') }}</TableHead>
                            <TableHead class="text-end">{{ t('customers::customers.column_actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="customer in props.customers.data" :key="customer.id">
                            <TableCell class="font-medium">{{ customer.name }}</TableCell>
                            <TableCell dir="ltr" class="text-start tabular-nums">{{ customer.phone }}</TableCell>
                            <TableCell class="font-mono tabular-nums">${{ Number(customer.debt_balance).toFixed(2) }}</TableCell>
                            <TableCell class="tabular-nums">{{ customer.loyalty_points }}</TableCell>
                            <TableCell class="text-end">
                                <Link :href="show.url({ customer: customer.id })">
                                    <Button variant="outline" size="sm">{{ t('customers::customers.view') }}</Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <PaginationLinks
                v-if="props.customers.last_page > 1"
                :current-page="props.customers.current_page"
                :last-page="props.customers.last_page"
                :total="props.customers.total"
                :per-page="props.customers.per_page"
                :href-for="(page: number) => indexRoute.url({ query: { page } })"
            />
        </template>
    </div>
</template>
