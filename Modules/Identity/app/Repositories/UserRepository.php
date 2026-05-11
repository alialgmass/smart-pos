<?php

namespace Modules\Identity\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function paginateForTenant(int $tenantId, int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, User>
     */
    public function allForTenant(int $tenantId): Collection
    {
        return User::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();
    }
}
