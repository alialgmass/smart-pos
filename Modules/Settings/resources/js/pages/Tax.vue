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
    <Head :title="t('settings::tax.title')" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
        <PageHeader
            :title="t('settings::tax.title')"
            :description="t('settings::tax.subtitle')"
        />

        <form @submit.prevent="save" class="grid gap-5 rounded-xl border bg-card p-6">
            <div class="grid gap-2">
                <Label for="name">{{ t('settings::tax.name') }}</Label>
                <Input id="name" v-model="form.name" />
            </div>
            <div class="grid gap-2">
                <Label for="rate">{{ t('settings::tax.rate') }}</Label>
                <Input id="rate" v-model="form.rate" type="number" step="0.01" min="0" max="100" />
            </div>
            <div class="grid gap-2">
                <Label for="apply_to">{{ t('settings::tax.apply_to') }}</Label>
                <select
                    id="apply_to"
                    v-model="form.apply_to"
                    class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="all">{{ t('settings::tax.apply_all') }}</option>
                    <option value="food">{{ t('settings::tax.apply_food') }}</option>
                    <option value="beverage">{{ t('settings::tax.apply_beverage') }}</option>
                    <option value="merchandise">{{ t('settings::tax.apply_merchandise') }}</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <Checkbox
                    id="enabled"
                    :checked="form.enabled"
                    @update:checked="(value: boolean) => (form.enabled = value)"
                />
                <Label for="enabled" class="font-normal">{{ t('settings::tax.enabled') }}</Label>
            </div>
            <div>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    {{ t('settings::tax.save') }}
                </Button>
            </div>
        </form>
    </div>
</template>
