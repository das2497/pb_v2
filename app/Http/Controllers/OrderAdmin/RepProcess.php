<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Rep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RepProcess extends Controller
{
    public function index()
    {

        $reps = Rep::paginate(10);

        return view('order-admin/add-rep', [
            'reps' => $reps
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'contact' => 'required',
            'type' => 'required',
            'password' => 'required',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'rep',
            'password' => Hash::make($request->password),
        ]);

        Rep::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact,
            'type' => $request->type,
            'access' => 'off',
            'password' => $request->password,
        ]);

        return redirect('/order-admin/add-rep')->with('success', 'Successfully Added!');
    }

    public function delete(Request $request)
    {
        $rep = Rep::find($request->id);
        $rep->delete();

        return redirect('/order-admin/add-rep')->with('success', 'Successfully Deleted!');
    }
}
