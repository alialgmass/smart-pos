<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
    <Head :title="t('settings::invoice.title')" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
        <PageHeader
            :title="t('settings::invoice.title')"
            :description="t('settings::invoice.subtitle')"
        />

        <form @submit.prevent="save" class="grid gap-5 rounded-xl border bg-card p-6">
            <div class="grid gap-2">
                <Label for="prefix">{{ t('settings::invoice.prefix') }}</Label>
                <Input id="prefix" v-model="form.prefix" />
            </div>
            <div class="grid gap-2">
                <Label for="format">{{ t('settings::invoice.format') }}</Label>
                <Input id="format" v-model="form.format" class="font-mono" dir="ltr" />
                <p class="text-xs text-muted-foreground">{{ t('settings::invoice.format_hint') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Checkbox
                    id="show_logo"
                    :checked="form.show_logo"
                    @update:checked="(value: boolean) => (form.show_logo = value)"
                />
                <Label for="show_logo" class="font-normal">{{ t('settings::invoice.show_logo') }}</Label>
            </div>
            <div class="flex items-center gap-3">
                <Checkbox
                    id="show_address"
                    :checked="form.show_address"
                    @update:checked="(value: boolean) => (form.show_address = value)"
                />
                <Label for="show_address" class="font-normal">{{ t('settings::invoice.show_address') }}</Label>
            </div>
            <div class="grid gap-2">
                <Label for="footer_text">{{ t('settings::invoice.footer_text') }}</Label>
                <textarea
                    id="footer_text"
                    v-model="form.footer_text"
                    class="min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
            </div>
            <div>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    {{ t('settings::invoice.save') }}
                </Button>
            </div>
        </form>
    </div>
</template>
