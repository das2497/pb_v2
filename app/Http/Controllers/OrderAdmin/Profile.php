<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Profile extends Controller
{
    public function index()
    {
        return view('order-admin.profile');
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $validated = $request->validate((new UpdateProfileDetailsRequest())->rules());

        $request->user()->update($validated);

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate((new UpdatePasswordRequest())->rules());

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
