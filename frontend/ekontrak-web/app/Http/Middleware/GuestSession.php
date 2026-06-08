<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestSession
{
    /**
     * If user already has an API token in session, redirect to dashboard.
     * Applied to guest-only routes (login, register).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('api_token')) {
            if ((session('user.force_password_change') ?? false) === true) {
                return redirect()->route('change-password');
            }

            return redirect('/');
        }

        return $next($request);
    }
}
