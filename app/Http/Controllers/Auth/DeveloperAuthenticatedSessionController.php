<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DeveloperLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeveloperAuthenticatedSessionController extends Controller
{
    /**
     * Show the private developer login screen.
     *
     * An already authenticated user is sent back to the space matching
     * the role, so a developer never lands on the general dashboard.
     */
    public function create(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isDeveloper()) {
            return redirect()->route('developer.dashboard');
        }

        return view('auth.developer-login');
    }

    public function store(DeveloperLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('developer.login')->with('error', 'Votre compte a été désactivé.');
        }

        return redirect()->intended(route('developer.dashboard', absolute: false));
    }
}
