<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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

interface Customer {
    id: number;
    name: string;
    phone: string;
    debt_balance: string;
    loyalty_points: number;
}

const props = defineProps<{
    customers: {
        data: Customer[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
        };
    };
}>();

const form = useForm({
    name: '',
    phone: '',
});

const createCustomer = () => {
    form.post(route('customers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Customers" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Customers</h1>

            <Dialog>
                <DialogTrigger as-child>
                    <Button>Add Customer</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create Customer</DialogTitle>
                        <DialogDescription>
                            Add a new customer to your directory.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createCustomer" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" required />
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" />
                                Save
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Phone</TableHead>
                        <TableHead>Debt Balance</TableHead>
                        <TableHead>Loyalty Points</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="customer in customers.data" :key="customer.id">
                        <TableCell class="font-medium">{{ customer.name }}</TableCell>
                        <TableCell>{{ customer.phone }}</TableCell>
                        <TableCell>${{ customer.debt_balance }}</TableCell>
                        <TableCell>{{ customer.loyalty_points }}</TableCell>
                        <TableCell class="text-right">
                            <Link :href="route('customers.show', customer.id)">
                                <Button variant="outline" size="sm">View</Button>
                            </Link>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-if="customers.meta.last_page > 1" class="flex justify-center gap-2">
            <Link
                v-for="page in customers.meta.last_page"
                :key="page"
                :href="route('customers.index', { page })"
                class="rounded-md px-3 py-1 text-sm"
                :class="page === customers.meta.current_page ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
