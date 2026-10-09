<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Rep
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return redirect('/');
        } elseif (Auth::user()->role === 's_admin') {
            return redirect('/super-admin/dashboard');
        } elseif (Auth::user()->role === 'o_admin' || Auth::user()->role === 'admin' || Auth::user()->role === 'view' || Auth::user()->role === 'sales_admin') {
            return redirect('/order-admin/dashboard');
        } elseif (Auth::user()->role === 'rep') {
            return $next($request);
        } elseif (Auth::user()->role === 'shop') {
            return redirect('/shop/dashboard');
        } else {
            return redirect('/404');
        }
    }
}
