<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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

interface Category {
    id: number;
    name: string;
    sort_order: number;
    products_count: number;
}

const props = defineProps<{
    categories: Category[];
}>();

const form = useForm({
    name: '',
});

const createCategory = () => {
    form.post(route('inventory.categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const deleteCategory = (category: Category) => {
    if (category.products_count > 0) {
        return;
    }

    if (confirm(`Delete category "${category.name}"?`)) {
        useForm({}).delete(route('inventory.categories.destroy', category.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Categories" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Categories</h1>

            <Dialog>
                <DialogTrigger as-child>
                    <Button>Add Category</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create Category</DialogTitle>
                        <DialogDescription>
                            Add a new product category.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createCategory" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
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
                        <TableHead>Order</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Products</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="category in categories" :key="category.id">
                        <TableCell>{{ category.sort_order }}</TableCell>
                        <TableCell class="font-medium">{{ category.name }}</TableCell>
                        <TableCell>{{ category.products_count }}</TableCell>
                        <TableCell class="text-right">
                            <Button
                                variant="destructive"
                                size="sm"
                                :disabled="category.products_count > 0"
                                @click="deleteCategory(category)"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
