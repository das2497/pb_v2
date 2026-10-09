<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Products;
use App\Models\Routes;
use App\Models\Shops;
use App\Services\GoogleSheetService3;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoogleSheetController3 extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService3 $googleSheetService)
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
    // Fetch all shops in one query and group by route_type and then route_id
    $all_shops_grouped = Shops::join('routes', 'shops.morning_route', '=', 'routes.name')
        ->whereIn('routes.type', ['Normal', 'Special', 'PBD'])
        ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id', 'routes.type as route_type')
        ->orderBy('routes.id', 'asc')
        ->get()
        ->groupBy('route_type')
        ->map(function ($group) {
            return $group->groupBy('route_id');
        });

    $normal_shops = $all_shops_grouped->get('Normal', collect());
    $special_shops = $all_shops_grouped->get('Special', collect());
    $pbd_shops = $all_shops_grouped->get('PBD', collect());

    $products = Products::orderBy('item_number', 'asc')->get();

    // Assuming Products model has a 'category' field, fetch item_numbers for "Icing Display Cakes"
    $icingItemNumbers = Products::where('category', 'Icing Display Cakes')
        ->pluck('item_number')
        ->toArray();

    $currentDate = new DateTime();

    // Fetch all relevant order data in a single query and key it for fast lookups
    $ordersData = DB::table('orders')
        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
        ->where('orders.time_period', '=', 'Morning')
        ->where('orders.status', '=', 'Processing')
        ->whereDate('orders.created_at', $currentDate)
        ->select('orders.shop as shop_code', 'carts.item_number', 'carts.qty', 'carts.remarke')
        ->get()
        ->keyBy(function ($item) {
            return $item->shop_code . '_' . $item->item_number;
        });

    // Precompute shop totals for "Icing Display Cakes" category
    $shopTotals = [];
    foreach ($ordersData as $key => $order) {
        list($shop_code, $item_number) = explode('_', $key);
        if (in_array($item_number, $icingItemNumbers)) {
            if (!isset($shopTotals[$shop_code])) {
                $shopTotals[$shop_code] = 0;
            }
            $shopTotals[$shop_code] += $order->qty;
        }
    }

    $data = [];

    // Add the header row
    $header = ['#' , 'Item Code' , 'Item'];
    foreach ($normal_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->sinhala_name . ' (' . $shop->name . ')';
            $header[] = 'Remark';
        }
        $header[] = $shops->first()->route_name; // Add the route name once after the last shop of that route
    }
    $header[] = 'Normal Route Total';

    foreach ($special_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->sinhala_name . ' (' . $shop->name . ')';
            $header[] = 'Remark';
        }
        $header[] = $shops->first()->route_name; // Add the route name once after the last shop of that route
    }
    $header[] = 'Special Route Total';

    foreach ($pbd_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->sinhala_name . ' (' . $shop->name . ')';
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
        $row[] = $product->item_number;
        $row[] = $product->name_sinhala;

        // Loop through Normal Shops by Route
        foreach ($normal_shops as $route_id => $shops) {
            $routeQty = 0; // Initialize total quantity for the route
            foreach ($shops as $shop) {
                $key = $shop->branch_code . '_' . $product->item_number;
                $order = $ordersData->get($key);

                $qty = $order ? ($order->qty ?? 0) : 0;
                $remark = $order ? ($order->remarke ?? '') : '';

                $totalNormal += $qty;
                $routeQty += $qty; // Add to route total
                $row[] = $qty ? $qty : '';
                $row[] = $remark;
            }
            $row[] = $routeQty ? $routeQty : '';
        }
        $row[] = $totalNormal ? $totalNormal : '';

        // Loop through Special Shops
        foreach ($special_shops as $route_id => $shops) {
            $routeQty = 0; // Initialize total quantity for the route
            foreach ($shops as $shop) {
                $key = $shop->branch_code . '_' . $product->item_number;
                $order = $ordersData->get($key);

                $qty = $order ? ($order->qty ?? 0) : 0;
                $remark = $order ? ($order->remarke ?? '') : '';

                $totalSpecial += $qty;
                $routeQty += $qty; // Add to route total
                $row[] = $qty ? $qty : '';
                $row[] = $remark;
            }
            $row[] = $routeQty ? $routeQty : '';
        }
        $row[] = $totalSpecial ? $totalSpecial : '';

        // Loop through PBD Shops
        foreach ($pbd_shops as $route_id => $shops) {
            $routeQty = 0; // Initialize total quantity for the route
            foreach ($shops as $shop) {
                $key = $shop->branch_code . '_' . $product->item_number;
                $order = $ordersData->get($key);

                $qty = $order ? ($order->qty ?? 0) : 0;
                $remark = $order ? ($order->remarke ?? '') : '';

                $totalPBD += $qty;
                $routeQty += $qty; // Add to route total
                $row[] = $qty ? $qty : '';
                $row[] = $remark;
            }
            $row[] = $routeQty ? $routeQty : '';
        }
        $row[] = $totalPBD ? $totalPBD : '';

        $finalTotal = $totalNormal + $totalSpecial + $totalPBD;
        $row[] = $finalTotal ? $finalTotal : '';

        $data[] = $row;  // Add the product row to the data array
    }

    // Add the "Icing Display Cakes" total row at the bottom
    $icingRow = ['', '', 'Icing Display Cakes Total'];

    $totalNormal = 0;
    // Normal shops
    foreach ($normal_shops as $route_id => $shops) {
        $routeTotal = 0;
        foreach ($shops as $shop) {
            $total = $shopTotals[$shop->branch_code] ?? 0;
            $routeTotal += $total;
            $icingRow[] = $total ? $total : '';
            $icingRow[] = ''; // Empty remark
        }
        $icingRow[] = $routeTotal ? $routeTotal : '';
        $totalNormal += $routeTotal;
    }
    $icingRow[] = $totalNormal ? $totalNormal : '';

    $totalSpecial = 0;
    // Special shops
    foreach ($special_shops as $route_id => $shops) {
        $routeTotal = 0;
        foreach ($shops as $shop) {
            $total = $shopTotals[$shop->branch_code] ?? 0;
            $routeTotal += $total;
            $icingRow[] = $total ? $total : '';
            $icingRow[] = ''; // Empty remark
        }
        $icingRow[] = $routeTotal ? $routeTotal : '';
        $totalSpecial += $routeTotal;
    }
    $icingRow[] = $totalSpecial ? $totalSpecial : '';

    $totalPBD = 0;
    // PBD shops
    foreach ($pbd_shops as $route_id => $shops) {
        $routeTotal = 0;
        foreach ($shops as $shop) {
            $total = $shopTotals[$shop->branch_code] ?? 0;
            $routeTotal += $total;
            $icingRow[] = $total ? $total : '';
            $icingRow[] = ''; // Empty remark
        }
        $icingRow[] = $routeTotal ? $routeTotal : '';
        $totalPBD += $routeTotal;
    }
    $icingRow[] = $totalPBD ? $totalPBD : '';

    $finalTotal = $totalNormal + $totalSpecial + $totalPBD;
    $icingRow[] = $finalTotal ? $finalTotal : '';

    $data[] = $icingRow;

    // Clean the data to ensure valid UTF-8 for JSON serialization
    $data = $this->cleanForJson($data);

    // Send the data to the Google Sheet
    $range = 'Sheet1!A1';
    $this->googleSheetService->updateRow($range, $data);

    Logs::create([
        'type' => 'Refresh Morning Shop Report',
        'message' => 'Ordering admin refreshed morning shop report',
        'user' => Auth::user()->name,
    ]);

    return back()->with('success', 'Successfully updated morning shop report!');
}

/**
 * Recursively clean array data for valid UTF-8 to prevent json_encode failures.
 */
private function cleanForJson($data)
{
    return array_map(function ($item) {
        if (is_array($item)) {
            return $this->cleanForJson($item);
        } elseif (is_string($item)) {
            // Strip invalid UTF-8 sequences
            return mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        } elseif ($item === null) {
            return ''; // Replace null with empty string to avoid API errors
        } else {
            return $item;
        }
    }, $data);
}
}


