<?php

namespace Modules\Sales\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Sales\Models\Sale;

class SaleCompleted
{
    use Dispatchable;

    public function __construct(
        public Sale $sale,
    ) {}
}
