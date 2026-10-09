<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Logs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::user()->role === 's_admin') {
            Logs::create([
                'type' => 'Login',
                'message' => 'Super admin login',
                'user' => Auth::user()->name,
            ]);
            return redirect('/super-admin/dashboard');
        } elseif (Auth::user()->role === 'o_admin' || Auth::user()->role === 'admin' || Auth::user()->role === 'view' || Auth::user()->role === 'sales_admin') {
            Logs::create([
                'type' => 'Login',
                'message' => 'Ordering admin login',
                'user' => Auth::user()->name,
            ]);
            return redirect('/order-admin/dashboard');
        } elseif (Auth::user()->role === 'rep') {
            Logs::create([
                'type' => 'Login',
                'message' => 'Rep login',
                'user' => Auth::user()->name,
            ]);
            return redirect('/rep/dashboard');
        } else {
            Logs::create([
                'type' => 'Login',
                'message' => 'Shop login',
                'user' => Auth::user()->name,
            ]);
            return redirect('/shop/dashboard');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
