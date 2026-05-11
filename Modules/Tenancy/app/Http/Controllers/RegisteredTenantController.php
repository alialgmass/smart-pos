<?php

namespace Modules\Tenancy\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Tenancy\Actions\RegisterTenantAction;
use Modules\Tenancy\DTOs\RegisterTenantData;
use Modules\Tenancy\Http\Requests\RegisterTenantRequest;

class RegisteredTenantController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Tenancy/RegisterTenant');
    }

    public function store(RegisterTenantRequest $request, RegisterTenantAction $action): RedirectResponse
    {
        $user = $action->execute(RegisterTenantData::fromArray($request->validated()));

        Auth::guard(config('fortify.guard', 'web'))->login($user);

        return redirect()
            ->route('tenant.register.check-inbox')
            ->with('status', __('Verification link sent.'));
    }

    public function checkInbox(): Response
    {
        return Inertia::render('auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }
}
