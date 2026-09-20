<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommercialMiddleware
{
    /**
     * Guards the commercial space: guests are sent to the commercial login,
     * deactivated accounts are logged out, and every other role is redirected
     * to the commercial login page so they can authenticate with the correct role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('commercial.login');
        }

        if (! auth()->user()->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('commercial.login')->with('error', 'Votre compte a été désactivé.');
        }

        if (! auth()->user()->isCommercial()) {
            return response('Accès réservé aux commerciaux.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
