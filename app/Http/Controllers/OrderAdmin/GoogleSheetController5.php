<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Products;
use App\Services\GoogleSheetExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Beverage shop report -> Google Sheet, the 5th OrderAdmin "refresh"
 * button. Same shape as GoogleSheetController3/4 (morning/evening shop
 * report), with the beverage report's own rules, which match
 * ShopReport::beverageReportData():
 *
 *  - shops are grouped by beverage_route, and only routes tagged
 *    time = 'Beverage' are joined, so a shop with no beverage route is
 *    left out of the sheet entirely;
 *  - product rows are limited to items available for the Beverage order
 *    time (Products::forPeriod);
 *  - orders are read with time_period = 'Beverage'.
 *
 * There is no "Icing Display Cakes Total" row here (that bottom row is
 * specific to the cake reports; beverage items are not in that
 * category).
 *
 * Unlike the other four, this report's spreadsheet ID has no hardcoded
 * production default - set GOOGLE_SHEET_BEVERAGE_SHOP_REPORT_ID in .env.
 * Until then the button reports the misconfiguration instead of writing
 * to the wrong sheet.
 */
class GoogleSheetController5 extends Controller
{
    public function updateGoogleSheet()
    {
        try {
            $googleSheetService = new GoogleSheetExportService(GoogleSheetExportService::BEVERAGE_SHOP_REPORT);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Beverage shop report spreadsheet is not configured. Set GOOGLE_SHEET_BEVERAGE_SHOP_REPORT_ID in .env.');
        }

        $currentDate = now()->toDateString();

        // Beverage routes only, grouped route type -> route id -> shops,
        // so each route gets its own total column.
        $all_shops_grouped = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.beverage_route')
            ->where('routes.time', 'Beverage')
            ->whereIn('routes.type', ['Normal', 'Special', 'PBD'])
            ->select('shops.*', 'routes.name as route_name', 'routes.id as route_id', 'routes.type as route_type')
            ->orderBy('routes.id')
            ->get()
            ->groupBy('route_type')
            ->map(fn ($group) => $group->groupBy('route_id'));

        $normal_shops = $all_shops_grouped->get('Normal', collect());
        $special_shops = $all_shops_grouped->get('Special', collect());
        $pbd_shops = $all_shops_grouped->get('PBD', collect());

        $products = Products::forPeriod('Beverage')
            ->orderBy('item_number', 'asc')
            ->get();

        $ordersData = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', '=', 'Beverage')
            ->where('orders.status', '=', 'Processing')
            ->whereDate('orders.created_at', $currentDate)
            ->select('orders.shop as shop_code', 'carts.item_number', 'carts.qty', 'carts.remarke')
            ->get()
            ->keyBy(fn ($item) => $item->shop_code . '_' . $item->item_number);

        $data = [];

        // Header row
        $header = ['#', 'Item Code', 'Item'];
        foreach ([[$normal_shops, 'Normal Route Total'], [$special_shops, 'Special Route Total'], [$pbd_shops, 'PBD Route Total']] as [$group, $label]) {
            foreach ($group as $shops) {
                foreach ($shops as $shop) {
                    $header[] = $shop->sinhala_name . ' (' . $shop->name . ')';
                    $header[] = 'Remark';
                }
                $header[] = $shops->first()->route_name;
            }
            $header[] = $label;
        }
        $header[] = 'Final Total';
        $data[] = $header;

        // Product rows
        foreach ($products as $index => $product) {
            $row = [$index + 1, $product->item_number, $product->name_sinhala];
            $finalTotal = 0;

            foreach ([$normal_shops, $special_shops, $pbd_shops] as $group) {
                $groupTotal = 0;
                foreach ($group as $shops) {
                    $routeQty = 0;
                    foreach ($shops as $shop) {
                        $order = $ordersData->get($shop->branch_code . '_' . $product->item_number);

                        $qty = $order ? ($order->qty ?? 0) : 0;
                        $remark = $order ? ($order->remarke ?? '') : '';

                        $groupTotal += $qty;
                        $routeQty += $qty;
                        $row[] = $qty ?: '';
                        $row[] = $remark;
                    }
                    $row[] = $routeQty ?: '';
                }
                $row[] = $groupTotal ?: '';
                $finalTotal += $groupTotal;
            }

            $row[] = $finalTotal ?: '';
            $data[] = $row;
        }

        $data = $this->cleanForJson($data);

        $googleSheetService->updateRow('Sheet1!A1', $data);

        Logs::create([
            'type' => 'Refresh Beverage Shop Report',
            'message' => 'Ordering admin refreshed beverage shop report',
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Successfully updated beverage shop report!');
    }

    /**
     * Recursively clean array data for valid UTF-8 to prevent json_encode
     * failures - same guard GoogleSheetController3/4 apply.
     */
    private function cleanForJson($data)
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->cleanForJson($item);
            } elseif (is_string($item)) {
                return mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            } elseif ($item === null) {
                return '';
            }

            return $item;
        }, $data);
    }
}
