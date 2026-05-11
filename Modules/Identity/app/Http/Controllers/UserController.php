<?php

namespace Modules\Identity\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Actions\CreateTenantUserAction;
use Modules\Identity\Actions\ToggleTenantUserActiveAction;
use Modules\Identity\Actions\UpdateTenantUserAction;
use Modules\Identity\Http\Requests\StoreTenantUserRequest;
use Modules\Identity\Http\Requests\UpdateTenantUserRequest;
use Modules\Identity\Repositories\UserRepository;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Identity/Users/Index', [
            'users' => $this->users->paginateForTenant((int) auth()->user()->tenant_id),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StoreTenantUserRequest $request, CreateTenantUserAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return redirect()->route('users.index')->with('status', __('User created.'));
    }

    public function update(UpdateTenantUserRequest $request, User $user, UpdateTenantUserAction $action): RedirectResponse
    {
        $action->execute($user, $request->validated());

        return redirect()->route('users.index')->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('status', __('User deleted.'));
    }

    public function toggleActive(User $user, ToggleTenantUserActiveAction $action): RedirectResponse
    {
        Gate::authorize('toggleActive', $user);

        $action->execute(auth()->user(), $user);

        return redirect()->route('users.index')->with('status', __('User status updated.'));
    }
}
