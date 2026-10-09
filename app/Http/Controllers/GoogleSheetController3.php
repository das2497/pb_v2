<?php

// app/Http/Controllers/GoogleSheetController.php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Routes;
use App\Models\Shops;
use App\Services\GoogleSheetService3;
use DateTime;
use Illuminate\Http\Request;
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
    // Cache shops (static, grouped by type and route)
    $groupedShops = Cache::remember('grouped_shops', 3600, function () {  // 1 hour TTL
        $allShops = Shops::join('routes', 'shops.morning_route', '=', 'routes.name')
            ->whereIn('routes.type', ['Normal', 'Special', 'PBD'])
            ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id', 'routes.type as route_type')
            ->orderBy('routes.id', 'asc')
            ->get();

        return $allShops->groupBy('route_type')->map(function ($typeGroup) {
            return $typeGroup->groupBy('route_id');
        });
    });

    $normal_shops = $groupedShops->get('Normal', collect());
    $special_shops = $groupedShops->get('Special', collect());
    $pbd_shops = $groupedShops->get('PBD', collect());

    // Cache products (static)
    $products = Cache::remember('products', 3600, function () {
        return Products::orderBy('item_number', 'asc')->get();
    });

    $currentDate = new DateTime();

    // Collect all shop branch codes for bulk order query
    $allShops = $normal_shops->flatten()->merge($special_shops->flatten())->merge($pbd_shops->flatten());
    $allBranchCodes = $allShops->pluck('branch_code')->unique();

    // Fetch all relevant orders in one query (dynamic, no cache here unless you add date-based key)
    $orders = DB::table('orders')
        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
        ->where('orders.time_period', '=', 'Morning')
        ->where('orders.status', '=', 'Processing')
        ->whereDate('orders.created_at', $currentDate)
        ->whereIn('orders.shop', $allBranchCodes)
        ->select('orders.shop', 'carts.item_number', 'carts.qty', 'carts.remarke')
        ->get();

    // Index orders for fast lookup
    $ordersByProductAndShop = [];
    foreach ($orders as $order) {
        $ordersByProductAndShop[$order->item_number][$order->shop] = [
            'qty' => $order->qty,
            'remarke' => $order->remarke,
        ];
    }

    $data = [];

    // Header row
    $header = ['#', 'Item Code' , 'Item'];
    foreach ($normal_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->name . ' Qty';
            $header[] = 'Remark';
        }
        $header[] = $shops->first()->route_name;
    }
    $header[] = 'Normal Route Total';

    foreach ($special_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->name . ' Qty';
            $header[] = 'Remark';
        }
        $header[] = $shops->first()->route_name;
    }
    $header[] = 'Special Route Total';

    foreach ($pbd_shops as $route_id => $shops) {
        foreach ($shops as $shop) {
            $header[] = $shop->name . ' Qty';
            $header[] = 'Remark';
        }
        $header[] = $shops->first()->route_name;
    }
    $header[] = 'PBD Route Total';

    $header[] = 'Final Total';
    $data[] = $header;

    // Product rows
    foreach ($products as $index => $product) {
        $row = [];
        $totalNormal = 0;
        $totalSpecial = 0;
        $totalPBD = 0;

        $row[] = $index + 1;
        $row[] = $product->item_number;
        $row[] =  $product->name_sinhala;

        foreach ($normal_shops as $route_id => $shops) {
            $routeQty = 0;
            foreach ($shops as $shop) {
                $orderData = $ordersByProductAndShop[$product->item_number][$shop->branch_code] ?? ['qty' => 0, 'remarke' => ''];
                $qty = $orderData['qty'];
                $remark = $orderData['remarke'];

                $totalNormal += $qty;
                $routeQty += $qty;
                $row[] = $qty;
                $row[] = $remark;
            }
            $row[] = $routeQty;
        }
        $row[] = $totalNormal;

        foreach ($special_shops as $route_id => $shops) {
            $routeQty = 0;
            foreach ($shops as $shop) {
                $orderData = $ordersByProductAndShop[$product->item_number][$shop->branch_code] ?? ['qty' => 0, 'remarke' => ''];
                $qty = $orderData['qty'];
                $remark = $orderData['remarke'];

                $totalSpecial += $qty;
                $routeQty += $qty;
                $row[] = $qty;
                $row[] = $remark;
            }
            $row[] = $routeQty;
        }
        $row[] = $totalSpecial;

        foreach ($pbd_shops as $route_id => $shops) {
            $routeQty = 0;
            foreach ($shops as $shop) {
                $orderData = $ordersByProductAndShop[$product->item_number][$shop->branch_code] ?? ['qty' => 0, 'remarke' => ''];
                $qty = $orderData['qty'];
                $remark = $orderData['remarke'];

                $totalPBD += $qty;
                $routeQty += $qty;
                $row[] = $qty;
                $row[] = $remark;
            }
            $row[] = $routeQty;
        }
        $row[] = $totalPBD;

        $row[] = $totalNormal + $totalSpecial + $totalPBD;
        $data[] = $row;
    }

    // Chunk the data and update in batches to optimize API performance
    $chunkSize = 50; // Adjust based on testing; smaller chunks reduce payload size but increase calls
    $chunks = array_chunk($data, $chunkSize);
    $rowOffset = 1;

    foreach ($chunks as $chunk) {
        $range = 'Sheet1!A' . $rowOffset;
        $this->googleSheetService->updateRow($range, $chunk);
        $rowOffset += count($chunk);
    }

    return response('Updated successfully');
}

}
