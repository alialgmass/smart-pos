<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
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
import { PageHeader, EmptyState, ConfirmDialog } from '@/components/shared';
import { store, destroy } from '@/routes/inventory/categories';

interface Category {
    id: number;
    name: string;
    sort_order: number;
    products_count: number;
}

const props = defineProps<{
    categories: Category[];
}>();

const { t } = useI18n();

const form = useForm({
    name: '',
});

const createCategory = () => {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const categoryToDelete = ref<Category | null>(null);
const deleting = ref(false);

const deleteCategory = () => {
    if (!categoryToDelete.value) {
        return;
    }

    deleting.value = true;
    router.delete(destroy.url({ category: categoryToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            categoryToDelete.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="t('inventory.categories.title')" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader :title="t('inventory.categories.title')">
            <template #actions>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button>{{ t('inventory.categories.add') }}</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>{{ t('inventory.categories.createTitle') }}</DialogTitle>
                            <DialogDescription>
                                {{ t('inventory.categories.createDescription') }}
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="createCategory" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">{{ t('common.name') }}</Label>
                                <Input id="name" v-model="form.name" required />
                            </div>

                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">
                                    <Spinner v-if="form.processing" />
                                    {{ t('common.save') }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </template>
        </PageHeader>

        <EmptyState
            v-if="categories.length === 0"
            :title="t('inventory.categories.empty')"
            :description="t('emptyState.description')"
        />

        <div v-else class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('inventory.categories.order') }}</TableHead>
                        <TableHead>{{ t('common.name') }}</TableHead>
                        <TableHead>{{ t('inventory.categories.products') }}</TableHead>
                        <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="category in categories" :key="category.id">
                        <TableCell>{{ category.sort_order }}</TableCell>
                        <TableCell class="font-medium">{{ category.name }}</TableCell>
                        <TableCell>{{ category.products_count }}</TableCell>
                        <TableCell class="text-right">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-destructive"
                                :disabled="category.products_count > 0"
                                @click="categoryToDelete = category"
                            >
                                {{ t('common.delete') }}
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <ConfirmDialog
            :open="categoryToDelete !== null"
            :processing="deleting"
            :description="
                categoryToDelete
                    ? t('inventory.categories.deleteConfirm', { name: categoryToDelete.name })
                    : undefined
            "
            @update:open="(open: boolean) => { if (!open) categoryToDelete = null }"
            @confirm="deleteCategory"
        />
    </div>
</template>
