<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    settings: {
        type?: string;
        width?: number;
        auto_cut?: boolean;
        copies?: number;
    };
}>();

const form = useForm({
    type: props.settings.type ?? 'thermal',
    width: props.settings.width ?? 80,
    auto_cut: props.settings.auto_cut ?? true,
    copies: props.settings.copies ?? 1,
});

const save = () => {
    form.put(route('settings.printer.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Printer Settings" />

    <div class="flex flex-col gap-6 p-6 max-w-2xl">
        <h1 class="text-2xl font-bold">Printer Settings</h1>

        <form @submit.prevent="save" class="grid gap-4">
            <div class="grid gap-2">
                <Label for="type">Printer Type</Label>
                <select
                    id="type"
                    v-model="form.type"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="thermal">Thermal</option>
                    <option value="a4">A4</option>
                    <option value="receipt">Receipt</option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label for="width">Paper Width (mm)</Label>
                <Input id="width" type="number" step="1" min="0" v-model="form.width" />
            </div>
            <div class="flex items-center gap-2">
                <input id="auto_cut" type="checkbox" v-model="form.auto_cut" class="rounded border-gray-300" />
                <Label for="auto_cut">Auto-cut after print</Label>
            </div>
            <div class="grid gap-2">
                <Label for="copies">Default Copies</Label>
                <Input id="copies" type="number" min="1" max="10" v-model="form.copies" />
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
