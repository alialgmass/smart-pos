<?php

namespace Modules\Tenancy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Shared\Support\CurrentTenant;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user === null) {
            app()->instance(CurrentTenant::class, new CurrentTenant);

            return $next($request);
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('status', __('Account disabled.'));
        }

        app()->instance(CurrentTenant::class, new CurrentTenant(
            $user->tenant_id,
            $user->tenant?->name,
        ));

        return $next($request);
    }
}
