<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSession
{
    /**
     * Redirect to login if no API token in session.
     * Applied to all authenticated routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('api_token')) {
            return redirect()->route('login')
                ->with('error', 'Sila log masuk untuk meneruskan.');
        }

        if ((session('user.force_password_change') ?? false) === true
            && ! $request->routeIs('change-password', 'change-password.update', 'logout')) {
            return redirect()->route('change-password')
                ->with('warning', 'Anda diwajibkan menukar kata laluan sebelum meneruskan.');
        }

        return $next($request);
    }
}
