<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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

interface TableData {
    id: number;
    name: string;
    capacity: number;
    status: number;
    position_x: number | null;
    position_y: number | null;
    active_order: { id: number; order_number: string } | null;
}

const props = defineProps<{
    tables: TableData[];
}>();

const form = useForm({
    name: '',
    capacity: '2',
    position_x: '',
    position_y: '',
});

const createTable = () => {
    form.post(route('restaurant.tables.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const statusLabel = (status: number) => {
    switch (status) {
        case 1: return 'Available';
        case 2: return 'Occupied';
        case 3: return 'Reserved';
        default: return 'Unknown';
    }
};

const statusVariant = (status: number) => {
    switch (status) {
        case 1: return 'success' as const;
        case 2: return 'warning' as const;
        case 3: return 'secondary' as const;
        default: return 'outline' as const;
    }
};
</script>

<template>
    <Head title="Tables" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Restaurant Tables</h1>

            <Dialog>
                <DialogTrigger as-child>
                    <Button>Add Table</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create Table</DialogTitle>
                        <DialogDescription>
                            Add a new dining table to the floor plan.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createTable" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Table Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="capacity">Capacity</Label>
                            <Input id="capacity" type="number" min="1" v-model="form.capacity" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="position_x">Position X</Label>
                                <Input id="position_x" type="number" v-model="form.position_x" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="position_y">Position Y</Label>
                                <Input id="position_y" type="number" v-model="form.position_y" />
                            </div>
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="table in tables"
                :key="table.id"
                class="rounded-lg border p-4 transition-shadow hover:shadow-md"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ table.name }}</h3>
                    <Badge :variant="statusVariant(table.status)">
                        {{ statusLabel(table.status) }}
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground">
                    Capacity: {{ table.capacity }}
                </p>
                <p v-if="table.active_order" class="mt-2 text-sm font-medium text-amber-600">
                    Active Order: {{ table.active_order.order_number }}
                </p>
            </div>
        </div>

        <div v-if="tables.length === 0" class="py-12 text-center text-muted-foreground">
            No tables yet. Create one to get started.
        </div>
    </div>
</template>
