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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Product {
    id: number;
    name: string;
    barcode: string | null;
    price: string;
    cost: string;
    stock_qty: string;
    min_stock: string;
    status: number;
    has_variants: boolean;
    category: { id: number; name: string } | null;
}

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    products: {
        data: Product[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
        };
    };
    categories: Category[];
}>();

const form = useForm({
    name: '',
    barcode: '',
    price: '',
    cost: '0',
    stock_qty: '0',
    min_stock: '0',
    category_id: '',
    status: '1',
});

const createProduct = () => {
    form.post(route('inventory.products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const statusBadge = (status: number) => {
    switch (status) {
        case 1: return { variant: 'success' as const, label: 'Active' };
        case 2: return { variant: 'secondary' as const, label: 'Inactive' };
        case 3: return { variant: 'warning' as const, label: 'Out of Stock' };
        default: return { variant: 'outline' as const, label: 'Unknown' };
    }
};
</script>

<template>
    <Head title="Products" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Products</h1>

            <Dialog>
                <DialogTrigger as-child>
                    <Button>Add Product</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create Product</DialogTitle>
                        <DialogDescription>
                            Add a new product to your inventory.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createProduct" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="barcode">Barcode</Label>
                            <Input id="barcode" v-model="form.barcode" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="category_id">Category</Label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">No Category</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="price">Price</Label>
                            <Input id="price" type="number" step="0.01" v-model="form.price" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="cost">Cost</Label>
                            <Input id="cost" type="number" step="0.01" v-model="form.cost" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="stock_qty">Stock Qty</Label>
                            <Input id="stock_qty" type="number" step="0.01" v-model="form.stock_qty" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="min_stock">Min Stock</Label>
                            <Input id="min_stock" type="number" step="0.01" v-model="form.min_stock" />
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
                        <TableHead>Barcode</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Stock</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in products.data" :key="product.id">
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell>{{ product.barcode ?? '-' }}</TableCell>
                        <TableCell>{{ product.category?.name ?? '-' }}</TableCell>
                        <TableCell>${{ product.price }}</TableCell>
                        <TableCell>{{ product.stock_qty }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusBadge(product.status).variant">
                                {{ statusBadge(product.status).label }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <Button variant="outline" size="sm">Edit</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-if="products.meta.last_page > 1" class="flex justify-center gap-2">
            <Link
                v-for="page in products.meta.last_page"
                :key="page"
                :href="route('inventory.products.index', { page })"
                class="rounded-md px-3 py-1 text-sm"
                :class="page === products.meta.current_page ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
