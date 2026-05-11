<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    settings: {
        prefix?: string;
        format?: string;
        show_logo?: boolean;
        show_address?: boolean;
        footer_text?: string;
    };
}>();

const form = useForm({
    prefix: props.settings.prefix ?? 'INV-',
    format: props.settings.format ?? '{prefix}{year}{month}{seq}',
    show_logo: props.settings.show_logo ?? true,
    show_address: props.settings.show_address ?? true,
    footer_text: props.settings.footer_text ?? '',
});

const save = () => {
    form.put(route('settings.invoice.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Invoice Settings" />

    <div class="flex flex-col gap-6 p-6 max-w-2xl">
        <h1 class="text-2xl font-bold">Invoice Settings</h1>

        <form @submit.prevent="save" class="grid gap-4">
            <div class="grid gap-2">
                <Label for="prefix">Invoice Prefix</Label>
                <Input id="prefix" v-model="form.prefix" />
            </div>
            <div class="grid gap-2">
                <Label for="format">Invoice Format</Label>
                <Input id="format" v-model="form.format" />
                <p class="text-xs text-muted-foreground">Use {prefix}, {year}, {month}, {seq} as placeholders</p>
            </div>
            <div class="flex items-center gap-2">
                <input id="show_logo" type="checkbox" v-model="form.show_logo" class="rounded border-gray-300" />
                <Label for="show_logo">Show logo on invoice</Label>
            </div>
            <div class="flex items-center gap-2">
                <input id="show_address" type="checkbox" v-model="form.show_address" class="rounded border-gray-300" />
                <Label for="show_address">Show store address</Label>
            </div>
            <div class="grid gap-2">
                <Label for="footer_text">Footer Text</Label>
                <textarea
                    id="footer_text"
                    v-model="form.footer_text"
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
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
