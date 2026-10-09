<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Rep;
use App\Models\RepAssignShop;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepAssignProcess extends Controller
{
    public function index(Request $request)
    {

        $reps = Rep::all();
        $shops = DB::table('shops')
            ->leftJoin('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->whereNull('rep_assign_shops.shop_id')
            ->select('shops.*')
            ->get();

        if ($request->has('search')) {
            $datas = DB::table('rep_assign_shops')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->join('shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                ->where('reps.name', 'like', '%' . $request->search . '%')
                ->orWhere('shops.name', 'like', '%' . $request->search . '%')
                ->select('reps.name as rep_name', 'reps.id as rep_id', 'shops.name as shop_name', 'shops.id as shop_id')
                ->paginate(10);

            return view('order-admin.rep-assign-shop', [
                'datas' => $datas,
                'reps' => $reps,
                'shops' => $shops
            ]);
        }

        $datas = DB::table('rep_assign_shops')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->join('shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->select('reps.name as rep_name', 'reps.id as rep_id', 'shops.name as shop_name', 'shops.id as shop_id')
            ->paginate(10);

        return view('order-admin.rep-assign-shop', [
            'datas' => $datas,
            'reps' => $reps,
            'shops' => $shops
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rep_id' => 'required',
            'shop_id' => 'required'
        ]);

        $result = RepAssignShop::where('rep_id', '=', $request->rep_id)
            ->where('shop_id', '=', $request->shop_id)
            ->get();

        if ($result->count() == 0) {
            RepAssignShop::create([
                'rep_id' => $request->rep_id,
                'shop_id' => $request->shop_id
            ]);

            return redirect('/order-admin/rep-assign')->with('success', 'Successfully Added!');
        } else {
            return redirect('/order-admin/rep-assign')->withErrors('This Record Already Added!');
        }
    }

    public function update_access(Request $request)
    {
        $rep_email = $request->rep_email;
        $access = $request->access;
        $stste = '';

        if ($access == null) {
            // dd($rep_email, 'off');
            $stste = 'off';
        } else {
            // dd($rep_email, $access);
            $stste = 'on';
        }

        Rep::where('email', '=', $rep_email)->update([
            'access' => $stste
        ]);

        return back()->with('success', 'Rep access update successfully');
    }

    public function update(Request $request)
    {
        $rep_id = $request->rep_id;
        $shop_id = $request->shop_id;
        RepAssignShop::where('shop_id', '=', $shop_id)
            ->update([
                'rep_id' => $rep_id
            ]);

        return back()->with('success', 'Rep assigned successfully');
    }
}
