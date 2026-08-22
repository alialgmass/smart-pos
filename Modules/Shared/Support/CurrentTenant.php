<?php

namespace Modules\Shared\Support;

final readonly class CurrentTenant
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
    ) {}

    public function isResolved(): bool
    {
        return $this->id !== null;
    }
}
