<?php

namespace Modules\Identity\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Fortify;
use Modules\Identity\Actions\AuthenticateUserAction;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => true,
            'canRegister' => true,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request, AuthenticateUserAction $action): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $authenticated = $action->execute(
            $request->input('email'),
            $request->input('password'),
            (bool) $request->boolean('remember'),
        );

        if (! $authenticated) {
            return back()->withErrors([
                Fortify::username() => __('auth.failed'),
            ])->onlyInput(Fortify::username());
        }

        session()->regenerate();

        return redirect()->intended(Fortify::redirects('login'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
