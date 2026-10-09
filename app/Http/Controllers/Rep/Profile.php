<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Rep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Controller
{
    public function view()
    {
        return view('rep.profile');
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

        Rep::where('email', '=', $user->email)
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
