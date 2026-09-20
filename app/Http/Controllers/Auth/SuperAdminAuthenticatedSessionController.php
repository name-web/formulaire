<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SuperAdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminAuthenticatedSessionController extends Controller
{
    /**
     * Show the Super Admin login screen.
     *
     * An already authenticated super admin is redirected to the private space.
     */
    public function create(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('super_admin.dashboard');
        }

        return view('auth.super-admin-login');
    }

    public function store(SuperAdminLoginRequest $request): RedirectResponse
    {
        $request->merge(['remember' => true]);

        $request->authenticate();
        $request->session()->regenerate();

        if (! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('super_admin.login')->with('error', 'Votre compte a été désactivé.');
        }

        return redirect()->intended(route('super_admin.dashboard', absolute: false));
    }
}
