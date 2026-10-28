{{--
    The export report table, rendered as a standalone fragment so the page's
    JS can fetch() and swap it in without a full page reload (see
    rep/export-report.blade.php). Grouped by route, each shop gets a
    qty+remark column pair, with route and grand totals.

    $products here is already the fully-filtered list (category match AND
    has at least one qty > 0 somewhere) - if it's empty, there is nothing to
    show in the body no matter what $shopsGroupedByRoute contains, so that
    drives the empty state rather than leaving a confusing table with
    headers/totals but zero rows.
--}}
@if ($products->isEmpty() || $shopsGroupedByRoute->isEmpty())
<div class="rep-order-empty-state">
    <div class="rep-order-empty-state-icon">{{ $hasCategories ? '📭' : '🏷️' }}</div>
    <h4>{{ $hasCategories ? 'No matching orders' : 'Pick a category to see items' }}</h4>
    <p class="text-muted mb-0">
        {{ $hasCategories
            ? 'Try a different date range, route, or category.'
            : 'Select one or more categories above, then press Search.' }}
    </p>
</div>
@else
<div class="table-wrapper">
    <table class="table table-bordered m-2" id="ordersTable">
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2" style="min-width: 100px;">Item Code</th>
                <th rowspan="2" style="min-width: 280px;">Item</th>

                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                    <th colspan="{{ $routeShops->count() * 2 }}" class="text-center report-col-route">
                        <strong>{{ $routeName }}</strong>
                    </th>
                    <th rowspan="2" class="text-center fw-bold report-col-route-total">Route Total</th>
                @endforeach

                <th rowspan="2" class="text-center fw-bold report-col-grand-total">Grand Total</th>
            </tr>

            <tr>
                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                    @foreach ($routeShops as $shop)
                        <th class="text-center report-col-qty">Qty</th>
                        <th class="text-center report-col-rmk">Remark</th>
                    @endforeach
                @endforeach
            </tr>

            <tr>
                <th colspan="3"></th>
                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                    @foreach ($routeShops as $shop)
                        <th colspan="2" class="text-center">
                            <strong>{{ $shop->shop_name }}</strong><br>
                            <small>{{ $shop->branch_code }}</small>
                        </th>
                    @endforeach
                    <th></th> <!-- Route Total placeholder -->
                @endforeach
                <th></th>
            </tr>
        </thead>

        <tbody>
            @php
                $grandTotal = 0;
                $routeTotals = [];
            @endphp

            @foreach ($products as $product)
                @php
                    $rowTotal = 0;
                    $productRouteTotals = array_fill_keys($shopsGroupedByRoute->keys()->toArray(), 0);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $product->item_number }}</strong></td>
                    <td class="text-start">{{ $product->name_sinhala }}</td>

                    @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                        @php $routeQtySum = 0; @endphp
                        @foreach ($routeShops as $shop)
                            @php
                                $code = trim($shop->branch_code);
                                $qty = $aggregatedOrders[$code][$product->item_number]['qty'] ?? 0;
                                $remark = $aggregatedOrders[$code][$product->item_number]['remark'] ?? '';

                                $rowTotal += $qty;
                                $routeQtySum += $qty;
                                $productRouteTotals[$routeName] += $qty;
                            @endphp
                            <td class="text-center report-col-qty {{ $qty > 0 ? '' : 'report-zero' }}">{{ $qty }}</td>
                            <td class="report-col-rmk text-start">{{ $remark }}</td>
                        @endforeach
                        <td class="text-center fw-bold report-col-route-total">{{ $routeQtySum }}</td>
                    @endforeach

                    @php $grandTotal += $rowTotal; @endphp
                    <td class="text-center fw-bold report-col-grand-total">{{ $rowTotal }}</td>
                </tr>

                @php
                    foreach ($productRouteTotals as $route => $total) {
                        $routeTotals[$route] = ($routeTotals[$route] ?? 0) + $total;
                    }
                @endphp
            @endforeach
        </tbody>

        <tfoot class="fw-bold">
            <tr>
                <td colspan="3" class="text-end report-col-grand-total">Grand Total</td>

                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                    @foreach ($routeShops as $shop)
                        @php $code = trim($shop->branch_code); @endphp
                        <td colspan="2" class="text-center report-col-qty report-col-grand-total">
                            {{ $columnTotals[$code] ?? 0 }}
                        </td>
                        <td class="report-col-rmk report-col-grand-total"></td>
                    @endforeach
                    <td class="text-center report-col-route-total report-col-grand-total">
                        {{ $routeTotals[$routeName] ?? 0 }}
                    </td>
                @endforeach

                <td class="text-center report-col-grand-total">{{ $grandTotal }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif
