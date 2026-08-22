<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { Armchair, Plus, LoaderCircle, ReceiptText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface TableData {
    id: number;
    name: string;
    capacity: number;
    status: number;
    position_x: number | null;
    position_y: number | null;
    active_order: { id: number; order_number: string } | null;
}

const { t, te } = useI18n();

defineProps<{
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

const statusLabel = (status: number): string => {
    const map: Record<number, string> = {
        1: 'restaurant::tables.status_available',
        2: 'restaurant::tables.status_occupied',
        3: 'restaurant::tables.status_reserved',
    };

    return te(map[status]) ? t(map[status]) : t('restaurant::tables.status_available');
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
    <Head :title="t('restaurant::tables.title')" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <PageHeader :title="t('restaurant::tables.title')" />

            <Dialog>
                <DialogTrigger as-child>
                    <Button>
                        <Plus class="size-4" />
                        {{ t('restaurant::tables.add_table') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ t('restaurant::tables.create_table') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('restaurant::tables.create_table_description') }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createTable" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">{{ t('restaurant::tables.table_name') }}</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="capacity">{{ t('restaurant::tables.capacity') }}</Label>
                            <Input id="capacity" v-model="form.capacity" type="number" min="1" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="position_x">{{ t('restaurant::tables.position_x') }}</Label>
                                <Input id="position_x" v-model="form.position_x" type="number" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="position_y">{{ t('restaurant::tables.position_y') }}</Label>
                                <Input id="position_y" v-model="form.position_y" type="number" />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                                {{ t('restaurant::tables.save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <EmptyState
            v-if="tables.length === 0"
            :icon="Armchair"
            :title="t('restaurant::tables.empty_title')"
            :description="t('restaurant::tables.empty_description')"
        />

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="table in tables"
                :key="table.id"
                class="rounded-lg border p-4 transition-all hover:border-ring hover:shadow-md"
            >
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 text-lg font-semibold">
                        <Armchair class="size-4 text-muted-foreground" />
                        {{ table.name }}
                    </h3>
                    <Badge :variant="statusVariant(table.status)">
                        {{ statusLabel(table.status) }}
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground tabular-nums">
                    {{ t('restaurant::tables.capacity_label', { value: table.capacity }) }}
                </p>
                <p
                    v-if="table.active_order"
                    class="mt-2 flex items-center gap-1.5 text-sm font-medium text-amber-600 dark:text-amber-400"
                >
                    <ReceiptText class="size-3.5 shrink-0" />
                    {{ table.active_order.order_number }}
                </p>
            </div>
        </div>
    </div>
</template>
