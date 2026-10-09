<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Products;
use App\Models\Routes;
use App\Services\GoogleSheetService;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoogleSheetController extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
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
    // Fetch all routes in one query and group by type
    $all_routes_grouped = Routes::where('time', '=', 'Morning')
        ->whereIn('type', ['Normal', 'Special', 'PBD'])
        ->orderBy('id', 'asc')
        ->get()
        ->groupBy('type');

    $normal_routes = $all_routes_grouped->get('Normal', collect());
    $special_routes = $all_routes_grouped->get('Special', collect());
    $pbd_routes = $all_routes_grouped->get('PBD', collect());

    $products = Products::orderBy('item_number', 'asc')->get(); // Fetch products

    $currentDate = new DateTime();

    // Fetch all summarized qty data in a single query and key it for fast lookups
    $summaries = DB::table('orders')
        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
        ->join('shops', 'orders.shop', '=', 'shops.branch_code')
        ->whereDate('orders.created_at', $currentDate)
        ->where('orders.status', '=', 'Processing')
        ->where('orders.time_period', '=', 'Morning')
        ->select('carts.item_number', 'shops.morning_route', DB::raw('SUM(carts.qty) as total_qty'))
        ->groupBy('carts.item_number', 'shops.morning_route')
        ->get()
        ->keyBy(function ($item) {
            return $item->item_number . '_' . $item->morning_route;
        });

    $data = [];

    // Add the header row
    $header = ['#', 'Item Code | Item'];
    foreach ($normal_routes as $route) {
        $header[] = $route->name;
    }
    $header[] = 'Normal Route Total';

    foreach ($special_routes as $route) {
        $header[] = $route->name;
    }
    $header[] = 'Special Route Total';

    foreach ($pbd_routes as $route) {
        $header[] = $route->name;
    }
    $header[] = 'PBD Route Total';

    $header[] = 'Final Total';
    $data[] = $header;  // Add the header row to the data array

    // Add the product rows
    foreach ($products as $index => $product) {
        $row = [];
        $total1 = 0;
        $total2 = 0;
        $total3 = 0;

        $row[] = $index + 1; // The iteration index for the product
        $row[] = $product->item_number . ' | ' . $product->name_english;

        foreach ($normal_routes as $route) {
            $key = $product->item_number . '_' . $route->name;
            $qty = $summaries->get($key)->total_qty ?? 0;
            $total1 += $qty;
            $row[] = $qty;
        }

        $row[] = $total1;

        foreach ($special_routes as $route) {
            $key = $product->item_number . '_' . $route->name;
            $qty = $summaries->get($key)->total_qty ?? 0;
            $total2 += $qty;
            $row[] = $qty;
        }

        $row[] = $total2;

        foreach ($pbd_routes as $route) {
            $key = $product->item_number . '_' . $route->name;
            $qty = $summaries->get($key)->total_qty ?? 0;
            $total3 += $qty;
            $row[] = $qty;
        }

        $row[] = $total3;
        $row[] = $total1 + $total2 + $total3;

        $data[] = $row;  // Add the product row to the data array
    }

    // Send the data to the Google Sheet
    $range = 'Sheet1!A1';
    $this->googleSheetService->updateRow($range, $data);

    Logs::create([
        'type' => 'Refresh Morning Summary',
        'message' => 'Ordering admin refreshed morning summary',
        'user' => Auth::user()->name,
    ]);

    return back()->with('success', 'Successfully updated morning summary!');
    // return response($data);
}
}

