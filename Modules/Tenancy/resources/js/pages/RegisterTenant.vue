<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/tenant/register';
import { Form, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineOptions({
    layout: {
        title: 'Create your store workspace',
        description: 'Set up the owner account and start your 14-day trial',
    },
});
</script>

<template>
    <Head :title="t('tenancy::register.page_title')" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="store_name">{{ t('tenancy::register.store_name') }}</Label>
                <Input
                    id="store_name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="organization"
                    name="store_name"
                    :placeholder="t('tenancy::register.store_name_placeholder')"
                />
                <InputError :message="errors.store_name" />
            </div>

            <div class="grid gap-2">
                <Label for="owner_name">{{ t('tenancy::register.owner_name') }}</Label>
                <Input
                    id="owner_name"
                    type="text"
                    required
                    :tabindex="2"
                    autocomplete="name"
                    name="owner_name"
                    :placeholder="t('tenancy::register.owner_name_placeholder')"
                />
                <InputError :message="errors.owner_name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">{{ t('tenancy::register.email_address') }}</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="3"
                    autocomplete="email"
                    name="email"
                    placeholder="owner@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">{{ t('tenancy::register.password') }}</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password"
                    :placeholder="t('tenancy::register.password_placeholder')"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">{{ t('tenancy::register.confirm_password') }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="t('tenancy::register.confirm_password_placeholder')"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="6"
                :disabled="processing"
                data-test="register-tenant-button"
            >
                <Spinner v-if="processing" />
                {{ t('tenancy::register.create_store') }}
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            {{ t('tenancy::register.already_registered') }}
            <TextLink :href="login()" class="underline underline-offset-4" :tabindex="7">
                {{ t('tenancy::register.log_in') }}
            </TextLink>
        </div>
    </Form>
</template>
