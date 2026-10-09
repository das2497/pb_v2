<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShopProcess extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $datetime = Carbon::parse($today);
        $date = $datetime->format('Y-m-d');

        $morning_routes = Routes::where('time', '=', 'Morning')->get();
        $evening_routes = Routes::where('time', '=', 'Evening')->get();

        $shops = null;

        if ($request->has('search') && $request->search != '') {
            $shops = Shops::join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                ->where('shops.name', 'like', '%' . $request->search . '%')
                ->select('shops.*', 'reps.name as rep_name')
                ->paginate(10);
        } else {
            $shops = Shops::join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                ->select('shops.*', 'reps.name as rep_name')
                ->paginate(10);
        }

        return view('order-admin.add-shop', [
            'morning_routes' => $morning_routes,
            'evening_routes' => $evening_routes,
            'shops' => $shops,
            'date' => $date
        ]);
    }

    public function store(Request $request)
    {
        $request->validate((new StoreShopRequest())->rules());

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'shop',
            'password' => Hash::make($request->password)
        ]);

        Shops::create([
            'name' => $request->name,
            'sinhala_name' => $request->name_sinhala,
            'branch_code' => $request->branch_code,
            'email' => $request->email,
            'contact' => $request->contact,
            'price_range' => $request->price_range,
            'order_time' => $request->order_time,
            'morning_route' => $request->morning_route,
            'evening_route' => $request->evening_route,
            'type' => $request->type,
        ]);

        return redirect('/order-admin/add-shop')->with('success', 'Successfully Added!');
    }

    public function update(Request $request, $id)
    {
        $request->validate((new UpdateShopRequest())->rules());

        $shop = Shops::findOrFail($id);
        $shop->branch_code = $request->branch_code;
        $shop->email = $request->email;
        $shop->price_range = $request->price_range;
        $shop->morning_route = $request->morning_route;
        $shop->evening_route = $request->evening_route;
        $shop->order_time = $request->order_time;
        $shop->type = $request->type;
        $shop->save();

        $user = User::where('email', '=', $request->email)->first();

        if ($user) {
            // Update user with matching name and role as 'shop'
            User::where('name', '=', $request->name)
                ->where('role', '=', 'shop')
                ->update([
                    'email' => $request->email
                ]);
        } else {
            // Create a new user with role 'shop'. Password is random and
            // never communicated — the shop uses "Forgot password" to set
            // their own (previously every auto-created account shared one
            // hardcoded password hash committed in source).
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'shop',
                'password' => Hash::make(Str::random(40)),
            ]);
        }


        return redirect()->back()->with('success', 'Shop updated successfully ');
    }
    
        function delete($id)
    {       
        $shop = Shops::findOrFail($id);
        $user = User::where('email', '=', $shop->email)->first();

        if ($user) {
            $user->delete();
        }

        $shop->delete();

        return redirect()->back()->with('success', 'Shop deleted successfully');
    }
}
