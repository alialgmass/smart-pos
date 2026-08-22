<?php

namespace Modules\Customers\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Customers\Repositories\CustomerRepository;

class CustomerSearchController extends Controller
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function search(): JsonResponse
    {
        $query = request()->get('q', '');

        $results = $this->customers->search(
            (int) auth()->user()->tenant_id,
            $query,
        );

        return response()->json(['customers' => $results]);
    }
}
