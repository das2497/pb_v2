<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces the 5 duplicate role-gating middleware classes
 * (Login/SuperAdmin/OrderAdmin/Rep/Shop), which were all the exact same
 * if/elseif chain over Auth::user()->role, differing only in which
 * branch called $next($request). Usage: `role:o_admin,admin,view,sales_admin`.
 */
class EnsureUserHasRole
{
    /**
     * Where a user who doesn't have one of the allowed roles gets bounced
     * to — their OWN dashboard, not a blanket 403. An unrecognized role
     * falls through to /404, matching current behavior.
     */
    public const DASHBOARDS = [
        's_admin' => '/super-admin/dashboard',
        'o_admin' => '/order-admin/dashboard',
        'admin' => '/order-admin/dashboard',
        'view' => '/order-admin/dashboard',
        'sales_admin' => '/order-admin/dashboard',
        'rep' => '/rep/dashboard',
        'shop' => '/shop/dashboard',
    ];

    public static function dashboardFor(?string $role): string
    {
        return self::DASHBOARDS[$role] ?? '/404';
    }

    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $role = Auth::user()->role;

        if (in_array($role, $allowedRoles, true)) {
            return $next($request);
        }

        return redirect(self::dashboardFor($role));
    }
}
