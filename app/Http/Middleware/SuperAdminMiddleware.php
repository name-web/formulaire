<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('super_admin.login');
        }

        if (! auth()->user()->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('super_admin.login')->with('error', 'Votre compte a été désactivé.');
        }

        if (! auth()->user()->isSuperAdmin()) {
            return response('Accès réservé au Super Administrateur.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
