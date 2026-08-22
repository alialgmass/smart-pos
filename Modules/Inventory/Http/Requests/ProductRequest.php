<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Inventory\Models\Product;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique((new Product)->getTable(), 'barcode')
                    ->whereNull('deleted_at')
                    ->ignore($productId),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'integer', 'in:1,2,3'],
            'has_variants' => ['nullable', 'boolean'],
        ];
    }
}
