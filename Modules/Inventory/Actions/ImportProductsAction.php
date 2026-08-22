<?php

namespace Modules\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;

class ImportProductsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{valid: array<int, array<string, mixed>>, errors: array<int, array{row: int, message: string}>}
     */
    public function preview(array $rows): array
    {
        $valid = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if (empty($row['name'])) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Product name is required.'];

                continue;
            }

            if (empty($row['price']) || ! is_numeric($row['price'])) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Price must be a valid number.'];

                continue;
            }

            $valid[] = [
                'name' => $row['name'],
                'barcode' => $row['barcode'] ?? null,
                'price' => (float) $row['price'],
                'cost' => isset($row['cost']) ? (float) $row['cost'] : 0,
                'stock_qty' => isset($row['stock_qty']) ? (float) $row['stock_qty'] : 0,
                'min_stock' => isset($row['min_stock']) ? (float) $row['min_stock'] : 0,
                'category_id' => ! empty($row['category_id']) ? (int) $row['category_id'] : null,
                'status' => ! empty($row['status']) ? (int) $row['status'] : 1,
            ];
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * @param  array<int, array<string, mixed>>  $validRows
     */
    public function confirm(array $validRows): int
    {
        return DB::transaction(function () use ($validRows): int {
            $count = 0;

            foreach ($validRows as $row) {
                Product::create($row);
                $count++;
            }

            return $count;
        });
    }
}
