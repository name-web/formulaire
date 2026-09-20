<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Always persist admin sessions (long-lived session + remember cookie).
        $request->merge(['remember' => true]);

        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Accès réservé aux administrateurs.');
        }

        if ($user->isSuperAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Veuillez utiliser votre espace dédié pour vous connecter.');
        }

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Votre compte a été désactivé.');
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
