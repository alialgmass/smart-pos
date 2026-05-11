<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
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
import users from '@/routes/users';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: string[];
    email_verified_at: string | null;
}

defineProps<{
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
    form.post(users.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="User Management" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Users</h1>

            <Dialog>
                <DialogTrigger as-child>
                    <Button>Add User</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create User</DialogTitle>
                        <DialogDescription>
                            Add a new user to your store with role assignment.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="createUser" class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="role">Role</Label>
                            <Select id="role" v-model="form.role" required>
                                <option
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </option>
                            </Select>
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" />
                                Save
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Role</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Verified</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{ user.name }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>
                            <Badge variant="outline">
                                {{ user.roles[0] }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="user.is_active ? 'success' : 'secondary'">
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="user.email_verified_at ? 'success' : 'warning'">
                                {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <Button variant="outline" size="sm">Edit</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div v-if="users.meta.last_page > 1" class="flex justify-center gap-2">
            <Link
                v-for="page in users.meta.last_page"
                :key="page"
                :href="users.index.url()"
                class="rounded-md px-3 py-1 text-sm"
                :class="page === users.meta.current_page ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
