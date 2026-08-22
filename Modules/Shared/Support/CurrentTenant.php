<?php

namespace Modules\Shared\Support;

use Illuminate\Support\Facades\Auth;

final class CurrentTenant
{
    public function __construct(
        private readonly ?int $tenantId = null,
        public readonly ?string $name = null,
    ) {}
    public static function make(){
        return new self(null,null);
    }

    public ?int $id {
        get => $this->tenantId ?? (Auth::hasUser() ? Auth::user()?->tenant_id : null);
    }

    public function isResolved(): bool
    {
        return $this->id !== null;
    }
}
