<?php

namespace Modules\Shared\DTOs;

use Illuminate\Support\Collection;

final readonly class PaginatedResultData
{
    /**
     * @param  Collection<int, mixed>  $items
     */
    public function __construct(
        public Collection $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
