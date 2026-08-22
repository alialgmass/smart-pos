<?php

namespace Modules\Customers\Actions;

use Modules\Customers\Models\Customer;

class CreateCustomerAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Customer
    {
        return Customer::create($data);
    }
}
