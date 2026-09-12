<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeveloperMiddleware
{
    /**
     * Guards the developer space: guests are sent to the developer login,
     * deactivated accounts are logged out, and every other role is refused.
     * It also authenticates the request, so the space stays independent from
     * the general `auth` middleware (which redirects to /login).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('developer.login');
        }

        if (! auth()->user()->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('developer.login')->with('error', 'Votre compte a été désactivé.');
        }

        if (! auth()->user()->isDeveloper()) {
            abort(403, 'Accès réservé aux développeurs.');
        }

        return $next($request);
    }
}
