<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guest-only gate for the login page: lets guests through, bounces an
 * already-authenticated user to their own dashboard (shares the mapping
 * with EnsureUserHasRole instead of keeping its own separate copy).
 */
class Login
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        return redirect(EnsureUserHasRole::dashboardFor(Auth::user()->role));
    }
}
