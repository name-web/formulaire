<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CommercialLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommercialAuthenticatedSessionController extends Controller
{
    /**
     * Show the private commercial login screen.
     *
     * An already authenticated commercial is redirected to the commercial space.
     */
    public function create(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isCommercial()) {
            return redirect()->route('commercial.dashboard');
        }

        return view('auth.commercial-login');
    }

    public function store(CommercialLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('commercial.login')->with('error', 'Votre compte a été désactivé.');
        }

        return redirect()->intended(route('commercial.dashboard', absolute: false));
    }
}
