<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Shops extends Controller
{
    public function index()
    {
        $shops = DB::table('rep_assign_shops')
            ->join('shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('shops.*')
            ->orderBy('branch_code')
            ->get();

        // Get today's date
        $today = Carbon::today();
        $datetime = Carbon::parse($today);
        $date = $datetime->format('Y-m-d');

        return view('rep.my-shops', [
            'shops' => $shops,
            'date' => $date,
        ]);
    }
}

