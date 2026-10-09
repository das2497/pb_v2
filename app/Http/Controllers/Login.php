<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Login extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('login');
    }


    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|max:255',
            'password' => 'required|min:8',
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('home'),
            'Username' => $validatedData['username'],
            'Password' => $validatedData['password'],
        ]);
    }
}

