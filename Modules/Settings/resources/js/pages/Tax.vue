<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    settings: {
        rate?: number;
        name?: string;
        apply_to?: string;
        enabled?: boolean;
    };
}>();

const form = useForm({
    rate: props.settings.rate ?? 0,
    name: props.settings.name ?? 'Tax',
    apply_to: props.settings.apply_to ?? 'all',
    enabled: props.settings.enabled ?? false,
});

const save = () => {
    form.put(route('settings.tax.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tax Settings" />

    <div class="flex flex-col gap-6 p-6 max-w-2xl">
        <h1 class="text-2xl font-bold">Tax Settings</h1>

        <form @submit.prevent="save" class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name">Tax Name</Label>
                <Input id="name" v-model="form.name" />
            </div>
            <div class="grid gap-2">
                <Label for="rate">Tax Rate (%)</Label>
                <Input id="rate" type="number" step="0.01" min="0" max="100" v-model="form.rate" />
            </div>
            <div class="grid gap-2">
                <Label for="apply_to">Apply To</Label>
                <select
                    id="apply_to"
                    v-model="form.apply_to"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="all">All Items</option>
                    <option value="food">Food Only</option>
                    <option value="beverage">Beverage Only</option>
                    <option value="merchandise">Merchandise Only</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input id="enabled" type="checkbox" v-model="form.enabled" class="rounded border-gray-300" />
                <Label for="enabled">Enable tax</Label>
            </div>
            <div>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Save
                </Button>
            </div>
        </form>
    </div>
</template>
