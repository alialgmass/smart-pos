<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
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
        return [
            'file' => ['nullable', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.barcode' => ['nullable', 'string', 'max:100'],
            'rows.*.cost' => ['nullable', 'numeric', 'min:0'],
            'rows.*.stock_qty' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
