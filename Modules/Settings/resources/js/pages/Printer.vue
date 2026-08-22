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
    <Head :title="t('settings::printer.title')" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
        <PageHeader
            :title="t('settings::printer.title')"
            :description="t('settings::printer.subtitle')"
        />

        <form @submit.prevent="save" class="grid gap-5 rounded-xl border bg-card p-6">
            <div class="grid gap-2">
                <Label for="type">{{ t('settings::printer.type') }}</Label>
                <select
                    id="type"
                    v-model="form.type"
                    class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="thermal">{{ t('settings::printer.type_thermal') }}</option>
                    <option value="a4">{{ t('settings::printer.type_a4') }}</option>
                    <option value="receipt">{{ t('settings::printer.type_receipt') }}</option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label for="width">{{ t('settings::printer.width') }}</Label>
                <Input id="width" v-model="form.width" type="number" step="1" min="0" />
            </div>
            <div class="flex items-center gap-3">
                <Checkbox
                    id="auto_cut"
                    :checked="form.auto_cut"
                    @update:checked="(value: boolean) => (form.auto_cut = value)"
                />
                <Label for="auto_cut" class="font-normal">{{ t('settings::printer.auto_cut') }}</Label>
            </div>
            <div class="grid gap-2">
                <Label for="copies">{{ t('settings::printer.copies') }}</Label>
                <Input id="copies" v-model="form.copies" type="number" min="1" max="10" />
            </div>
            <div>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    {{ t('settings::printer.save') }}
                </Button>
            </div>
        </form>
    </div>
</template>
