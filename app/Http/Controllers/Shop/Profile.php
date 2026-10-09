<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Controller
{
    public function index()
    {
        return view('shop.profile');
    }

    public function update(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:8',
            'confirm' => 'required',
        ]);

        if ($request->password != $request->confirm) {
            return back()->with('error', 'Password and confirm password should be same.');
        }

        $user = Auth::user();

        Shops::where('email', '=', $user->email)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

        User::where('email', '=', $user->email)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
