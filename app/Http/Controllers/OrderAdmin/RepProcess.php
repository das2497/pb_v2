<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepRequest;
use App\Models\Rep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $request->validate((new StoreRepRequest())->rules());

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
            'password' => Hash::make($request->password),
        ]);

        return redirect('/order-admin/add-rep')->with('success', 'Successfully Added!');
    }

    public function delete(Request $request)
    {
        $rep = Rep::find($request->id);

        // Unlike ShopProcess::delete(), this never cleaned up the rep's
        // login account or its rep_assign_shops rows (found via manual QA,
        // 2026-10-10 - the real dev dataset already had 190 such orphaned
        // rows from shop/rep deletes never cleaning this table up).
        $user = User::where('email', '=', $rep->email)->first();

        if ($user) {
            $user->delete();
        }

        DB::table('rep_assign_shops')->where('rep_id', $rep->id)->delete();

        $rep->delete();

        return redirect('/order-admin/add-rep')->with('success', 'Successfully Deleted!');
    }
}
