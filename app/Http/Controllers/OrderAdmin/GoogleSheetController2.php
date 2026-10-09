<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Products;
use App\Models\Routes;
use App\Services\GoogleSheetService2;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoogleSheetController2 extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService2 $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    public function read()
    {
        $range = 'Sheet1!A1:D10'; // Example range
        $data = $this->googleSheetService->readSheet($range);
        return response()->json($data);
    }

    public function insert()
    {
        $range = 'Sheet1!A1:D1'; // Example range
        $values = ['John Doe', 'johndoe@example.com', 'Developer', 'Company']; // Example values
        $this->googleSheetService->insertRow($range, $values);
        return response('Row inserted successfully');
    }

    public function update()
    {
        $range = 'Sheet1!A1:D1'; // Example range
        $values = ['Jane Doo Little', 'janedoe@example.com', 'Designer', 'Company']; // Example updated values
        $this->googleSheetService->updateRow($range, $values);
        return response('Row updated successfully');
    }

    public function delete()
    {
        $sheetId = 0; // Replace with your sheet ID
        $rowIndex = 1; // Index of the row to delete (0-based)
        $this->googleSheetService->deleteRow($sheetId, $rowIndex);
        return response('Row deleted successfully');
    }

public function updateGoogleSheet()
{
    // Date range for today (uses index better than whereDate)
    $start = Carbon::today()->startOfDay();
    $end   = Carbon::today()->endOfDay();

//    $start = Carbon::yesterday()->startOfDay();
//    $end   = Carbon::yesterday()->endOfDay();

    // Fetch routes for Evening in one go and split by type
    $routes = Routes::where('time', 'Evening')->get(['name', 'type']);

    $normalRoutes  = $routes->where('type', 'Normal')->pluck('name')->values()->all();
    $specialRoutes = $routes->where('type', 'Special')->pluck('name')->values()->all();
    $pbdRoutes     = $routes->where('type', 'PBD')->pluck('name')->values()->all();

    $allRouteNames = array_merge($normalRoutes, $specialRoutes, $pbdRoutes);

    // Fetch products with only needed columns
    $products = Products::orderBy('item_number', 'asc')->get(['item_number', 'name_english']);

    // Single aggregate query: sums by item_number and route
    $aggregates = DB::table('orders')
        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
        ->join('shops', 'orders.shop', '=', 'shops.branch_code')
        ->selectRaw('carts.item_number, shops.evening_route AS route_name, SUM(carts.qty) AS qty')
        ->whereBetween('orders.created_at', [$start, $end])
        ->where('orders.status', 'Processing')
        ->where('orders.time_period', 'Evening')
        ->when(!empty($allRouteNames), function ($q) use ($allRouteNames) {
            $q->whereIn('shops.evening_route', $allRouteNames);
        })
        ->groupBy('carts.item_number', 'shops.evening_route')
        ->get();        

    // Build a lookup matrix: [item_number][route_name] => qty
    $matrix = [];
    foreach ($aggregates as $row) {
        $matrix[$row->item_number][$row->route_name] = (int) $row->qty;
    }    

    // Build header
    $data = [];
    $header = ['#', 'Item Code | Item'];
    foreach ($normalRoutes as $r)  { $header[] = $r; }
    $header[] = 'Normal Route Total';
    foreach ($specialRoutes as $r) { $header[] = $r; }
    $header[] = 'Special Route Total';
    foreach ($pbdRoutes as $r)     { $header[] = $r; }
    $header[] = 'PBD Route Total';
    $header[] = 'Final Total';
    $data[] = $header;

    // Build rows
    foreach ($products as $index => $product) {
        $itemNo = $product->item_number;
        $row = [
            $index + 1,
            $itemNo . ' | ' . $product->name_english,
        ];

        $total1 = 0;
        foreach ($normalRoutes as $routeName) {
            $qty = $matrix[$itemNo][$routeName] ?? 0;
            $total1 += $qty;
            $row[] = $qty;
        }
        $row[] = $total1;

        $total2 = 0;
        foreach ($specialRoutes as $routeName) {
            $qty = $matrix[$itemNo][$routeName] ?? 0;
            $total2 += $qty;
            $row[] = $qty;
        }
        $row[] = $total2;

        $total3 = 0;
        foreach ($pbdRoutes as $routeName) {
            $qty = $matrix[$itemNo][$routeName] ?? 0;
            $total3 += $qty; // FIXED: accumulate into total3 (bug in original)
            $row[] = $qty;
        }
        $row[] = $total3;

        $row[] = $total1 + $total2 + $total3;

        $data[] = $row;
    }    

    // Send to Google Sheet in a single batch
    $range = 'Sheet1!A1';
    $this->googleSheetService->updateRow($range, $data);

    Logs::create([
        'type'    => 'Refresh Evening Summary',
        'message' => 'Ordering admin refreshed evening summary',
        'user'    => Auth::user()->name,
    ]);

    return back()->with('success', 'Successfully updated evening summary!');
}
}

