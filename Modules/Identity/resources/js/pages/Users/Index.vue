<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PaginationLinks from '@/components/shared/PaginationLinks.vue';
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
import usersRoutes from '@/routes/users';
import { Head, useForm } from '@inertiajs/vue3';
import { UserPlus, Users } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: string[];
    email_verified_at: string | null;
}

const { t } = useI18n();

const props = defineProps<{
    users: {
        data: User[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
        };
    };
    roles: string[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

const createUser = () => {
    form.post(usersRoutes.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head :title="t('identity::users.title')" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <PageHeader
                :title="t('identity::users.title')"
                :description="t('identity::users.subtitle')"
            />

            <Dialog>
                <DialogTrigger as-child>
                    <Button>
                        <UserPlus class="size-4" />
                        {{ t('identity::users.add_user') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ t('identity::users.create_user') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('identity::users.create_user_description') }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createUser" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">{{ t('identity::users.name') }}</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">{{ t('identity::users.email') }}</Label>
                            <Input id="email" v-model="form.email" type="email" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password">{{ t('identity::users.password') }}</Label>
                            <Input id="password" v-model="form.password" type="password" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="role">{{ t('identity::users.role') }}</Label>
                            <Select v-model="form.role" required>
                                <SelectTrigger id="role" class="w-full">
                                    <SelectValue :placeholder="t('identity::users.select_role')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="role in props.roles"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{ role }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">
                                {{ t('identity::users.save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <EmptyState
            v-if="props.users.data.length === 0"
            :icon="Users"
            :title="t('identity::users.empty_title')"
            :description="t('identity::users.empty_description')"
        />

        <template v-else>
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('identity::users.column_name') }}</TableHead>
                            <TableHead>{{ t('identity::users.column_email') }}</TableHead>
                            <TableHead>{{ t('identity::users.column_role') }}</TableHead>
                            <TableHead>{{ t('identity::users.column_status') }}</TableHead>
                            <TableHead>{{ t('identity::users.column_verified') }}</TableHead>
                            <TableHead class="text-end">{{ t('identity::users.column_actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.users.data" :key="user.id">
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell dir="ltr" class="text-start text-sm">{{ user.email }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    {{ user.roles[0] ?? t('identity::users.no_role') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="user.is_active ? 'success' : 'secondary'">
                                    {{ user.is_active ? t('identity::users.status_active') : t('identity::users.status_inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="user.email_verified_at ? 'success' : 'warning'">
                                    {{ user.email_verified_at ? t('identity::users.verified_yes') : t('identity::users.verified_pending') }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-end">
                                <Button variant="outline" size="sm">{{ t('identity::users.edit') }}</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <PaginationLinks
                v-if="props.users.meta.last_page > 1"
                :current-page="props.users.meta.current_page"
                :last-page="props.users.meta.last_page"
                :total="props.users.meta.total"
                :href-for="(page: number) => usersRoutes.index.url({ query: { page } })"
            />
        </template>
    </div>
</template>
