<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Votre compte a été désactivé.');
        }

        if (! auth()->user()->isAdmin()) {
            return response('Accès non autorisé.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
