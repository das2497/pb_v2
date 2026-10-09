<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Routes;
use App\Models\Shops;
use App\Services\GoogleSheetService4;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleSheetController4 extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService4 $googleSheetService)
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
        $normal_shops = Shops::join('routes', 'shops.evening_route', '=', 'routes.name')
            ->Where('routes.type', '=', 'Normal')
            ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id')
            ->orderBy('routes.id', 'asc')
            ->get()
            ->groupBy('route_id'); // Group by route_id to process shops by route

        $special_shops = Shops::join('routes', 'shops.evening_route', '=', 'routes.name')
            ->Where('routes.type', '=', 'Special')
            ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id')
            ->orderBy('routes.id', 'asc')
            ->get()
            ->groupBy('route_id');

        $pbd_shops = Shops::join('routes', 'shops.evening_route', '=', 'routes.name')
            ->Where('routes.type', '=', 'PBD')
            ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id')
            ->orderBy('routes.id', 'asc')
            ->get()
            ->groupBy('route_id');

        $products = Products::orderBy('item_number', 'asc')->get();

        $currentDate = new DateTime();

        $data = [];

        // Add the header row
        $header = ['#', 'Item Code | Item'];
        foreach ($normal_shops as $route_id => $shops) {
            foreach ($shops as $shop) {
                $header[] = $shop->name . ' Qty';
                $header[] = 'Remark';
            }
            $header[] = $shops->first()->route_name; // Add the route name once after the last shop of that route
        }
        $header[] = 'Normal Route Total';

        foreach ($special_shops as $route_id => $shops) {
            foreach ($shops as $shop) {
                $header[] = $shop->name . ' Qty';
                $header[] = 'Remark';
            }
            $header[] = $shops->first()->route_name; // Add the route name once after the last shop of that route
        }
        $header[] = 'Special Route Total';

        foreach ($pbd_shops as $route_id => $shops) {
            foreach ($shops as $shop) {
                $header[] = $shop->name . ' Qty';
                $header[] = 'Remark';
            }
            $header[] = $shops->first()->route_name; // Add the route name once after the last shop of that route
        }
        $header[] = 'PBD Route Total';

        $header[] = 'Final Total';
        $data[] = $header;  // Add the header row to the data array

        // Add the product rows
        foreach ($products as $index => $product) {
            $row = [];
            $totalNormal = 0;
            $totalSpecial = 0;
            $totalPBD = 0;

            $row[] = $index + 1; // The iteration index for the product
            $row[] = $product->item_number . ' | ' . $product->name_sinhala;

            // Loop through Normal Shops by Route
            foreach ($normal_shops as $route_id => $shops) {
                $routeQty = 0; // Initialize total quantity for the route
                foreach ($shops as $shop) {
                    $order = DB::table('orders')
                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                        ->where('orders.shop', '=', $shop->branch_code)
                        ->where('carts.item_number', '=', $product->item_number)
                        ->where('orders.time_period', '=', 'Evening')
                        ->where('orders.status', '=', 'Processing')
                        ->whereDate('orders.created_at',  $currentDate)
                        ->select('carts.qty', 'carts.remarke')
                        ->first();

                    $qty = $order->qty ?? 0;
                    $remark = $order->remarke ?? '';

                    $totalNormal += $qty;
                    $routeQty += $qty; // Add to route total
                    $row[] = $qty;
                    $row[] = $remark;
                }
                $row[] = $routeQty; // Add the route total after all shops in the route
            }
            $row[] = $totalNormal; // Add the total for all normal routes

            // Loop through Special Shops
            foreach ($special_shops as $route_id => $shops) {
                $routeQty = 0; // Initialize total quantity for the route
                foreach ($shops as $shop) {
                    $order = DB::table('orders')
                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                        ->where('orders.shop', '=', $shop->branch_code)
                        ->where('carts.item_number', '=', $product->item_number)
                        ->where('orders.time_period', '=', 'Evening')
                        ->where('orders.status', '=', 'Processing')
                        ->whereDate('orders.created_at',  $currentDate)
                        ->select('carts.qty', 'carts.remarke')
                        ->first();

                    $qty = $order->qty ?? 0;
                    $remark = $order->remarke ?? '';

                    $totalSpecial += $qty;
                    $routeQty += $qty; // Add to route total
                    $row[] = $qty;
                    $row[] = $remark;
                }
                $row[] = $routeQty; // Add the route total after all shops in the route
            }
            $row[] = $totalSpecial;

            // Loop through PBD Shops
            foreach ($pbd_shops as $route_id => $shops) {
                $routeQty = 0; // Initialize total quantity for the route
                foreach ($shops as $shop) {
                    $order = DB::table('orders')
                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                        ->where('orders.shop', '=', $shop->branch_code)
                        ->where('carts.item_number', '=', $product->item_number)
                        ->where('orders.time_period', '=', 'Evening')
                        ->where('orders.status', '=', 'Processing')
                        ->whereDate('orders.created_at',  $currentDate)
                        ->select('carts.qty', 'carts.remarke')
                        ->first();

                    $qty = $order->qty ?? 0;
                    $remark = $order->remarke ?? '';

                    $totalPBD += $qty;
                    $routeQty += $qty; // Add to route total
                    $row[] = $qty;
                    $row[] = $remark;
                }
                $row[] = $routeQty; // Add the route total after all shops in the route
            }
            $row[] = $totalPBD;

            $row[] = $totalNormal + $totalSpecial + $totalPBD; // Final total
            $data[] = $row;  // Add the product row to the data array
        }

        // Send the data to the Google Sheet
        $range = 'Sheet1!A1';
        $this->googleSheetService->updateRow($range, $data);

        return response('Updated successfully');
    }
}
