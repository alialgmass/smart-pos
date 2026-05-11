<?php

namespace Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Customers\Models\Customer;

class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:50',
                'unique:'.(new Customer)->getTable().',phone,'.($customerId ?? 'NULL').',id,tenant_id,'.auth()->user()->tenant_id,
            ],
        ];
    }
}
