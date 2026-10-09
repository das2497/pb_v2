<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Routes;
use App\Services\GoogleSheetService;
use DateTime;
use Illuminate\Http\Request;
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
        $normal_routes = Routes::where('type', '=', 'Normal')
            ->where('time', '=', 'Morning')->get(); // Fetch routes

        $special_routes = Routes::where('type', '=', 'Special')
            ->where('time', '=', 'Morning')->get(); // Fetch routes

        $pbd_routes = Routes::where('type', '=', 'PBD')
            ->where('time', '=', 'Morning')->get(); // Fetch routes

        $products = Products::orderBy('item_number', 'asc')->get(); // Fetch products

        $currentDate = new DateTime();

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
                $qty = DB::table('orders')
                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                    ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                    ->where('carts.item_number', $product->item_number)
                    ->whereDate('orders.created_at', $currentDate)
                    ->where('shops.morning_route', $route->name)
                    ->where('orders.status', '=', 'Processing')
                    ->where('orders.time_period', '=', 'Morning')
                    ->sum('carts.qty');
                $total1 += $qty;
                $row[] = $qty;
            }

            $row[] = $total1;

            foreach ($special_routes as $route) {
                $qty = DB::table('orders')
                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                    ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                    ->where('carts.item_number', $product->item_number)
                    ->whereDate('orders.created_at', $currentDate)
                    ->where('shops.morning_route', $route->name)
                    ->where('orders.status', '=', 'Processing')
                    ->where('orders.time_period', '=', 'Morning')
                    ->sum('carts.qty');
                $total2 += $qty;
                $row[] = $qty;
            }

            $row[] = $total2;

            foreach ($pbd_routes as $route) {
                $qty = DB::table('orders')
                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                    ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                    ->where('carts.item_number', $product->item_number)
                    ->whereDate('orders.created_at', $currentDate)
                    ->where('shops.morning_route', $route->name)
                    ->where('orders.status', '=', 'Processing')
                    ->where('orders.time_period', '=', 'Morning')
                    ->sum('carts.qty');
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

        return response('Updated successfully');
        // return response($data);
    }
}

