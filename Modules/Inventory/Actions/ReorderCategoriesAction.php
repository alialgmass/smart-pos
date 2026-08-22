<?php

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Category;

class ReorderCategoriesAction
{
    /**
     * @param  array<int>  $orderedIds
     */
    public function execute(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $index => $id) {
                Category::withoutGlobalScope('tenant')
                    ->where('id', $id)
                    ->update(['sort_order' => $index]);
            }
        });
    }
}
