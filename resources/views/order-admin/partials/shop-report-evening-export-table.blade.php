<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
{{-- Full, unpaginated report table consumed only by the "Export to Excel"
     JS on shop-report-evening via fetch(); never meant to be viewed
     directly. Same normal/special/pbd + qty/remark layout as
     shop-report-morning-export-table.blade.php, with an extra per-route
     total column within each type (see ShopReport::shopsByRouteAndType()).
     Kept as its own file so exportMorning()/exportEvening() can evolve
     independently. --}}
<table id="exportTable">
    <thead>
        <tr>
            <th class="rpt-header">#</th>
            <th class="rpt-header">Item Code | Item</th>

            @foreach ($header_normal as $route)
                @foreach ($route as $head)
                    <th class="rpt-header" colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
                @endforeach
                <th class="rpt-route-total">{{ $route->first()->route_name }} Total</th>
            @endforeach
            <th class="rpt-type-total-normal">Normal Route Total</th>

            @foreach ($header_special as $route)
                @foreach ($route as $head)
                    <th class="rpt-header" colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
                @endforeach
                <th class="rpt-route-total">{{ $route->first()->route_name }} Total</th>
            @endforeach
            <th class="rpt-type-total-special">Special Route Total</th>

            @foreach ($header_pbd as $route)
                @foreach ($route as $head)
                    <th class="rpt-header" colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
                @endforeach
                <th class="rpt-route-total">{{ $route->first()->route_name }} Total</th>
            @endforeach
            <th class="rpt-type-total-pbd">PBD Route Total</th>

            <th class="rpt-grand-total">Total</th>
        </tr>
        <tr>
            <th class="rpt-header"></th>
            <th class="rpt-header"></th>
            @foreach ($header_normal as $route)
                @foreach ($route as $head)
                    <th class="rpt-header">Qty</th>
                    <th class="rpt-header">Remark</th>
                @endforeach
                <th class="rpt-route-total"></th>
            @endforeach
            <th class="rpt-type-total-normal"></th>
            @foreach ($header_special as $route)
                @foreach ($route as $head)
                    <th class="rpt-header">Qty</th>
                    <th class="rpt-header">Remark</th>
                @endforeach
                <th class="rpt-route-total"></th>
            @endforeach
            <th class="rpt-type-total-special"></th>
            @foreach ($header_pbd as $route)
                @foreach ($route as $head)
                    <th class="rpt-header">Qty</th>
                    <th class="rpt-header">Remark</th>
                @endforeach
                <th class="rpt-route-total"></th>
            @endforeach
            <th class="rpt-type-total-pbd"></th>
            <th class="rpt-grand-total"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            @php
                $total_normal = 0;
                $total_special = 0;
                $total_pbd = 0;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                @foreach ($header_normal as $route)
                    @php $route_total = 0; @endphp
                    @foreach ($route as $head)
                        @php
                            $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                            $qty = $order['qty'] ?? 0;
                            $route_total += $qty;
                            $total_normal += $qty;
                        @endphp
                        <td class="{{ $qty ? 'rpt-qty-filled' : '' }}">{{ $qty }}</td>
                        <td>{{ $order['remark'] ?? '' }}</td>
                    @endforeach
                    <td class="rpt-route-total">{{ $route_total }}</td>
                @endforeach
                <td class="rpt-type-total-normal">{{ $total_normal }}</td>

                @foreach ($header_special as $route)
                    @php $route_total = 0; @endphp
                    @foreach ($route as $head)
                        @php
                            $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                            $qty = $order['qty'] ?? 0;
                            $route_total += $qty;
                            $total_special += $qty;
                        @endphp
                        <td class="{{ $qty ? 'rpt-qty-filled' : '' }}">{{ $qty }}</td>
                        <td>{{ $order['remark'] ?? '' }}</td>
                    @endforeach
                    <td class="rpt-route-total">{{ $route_total }}</td>
                @endforeach
                <td class="rpt-type-total-special">{{ $total_special }}</td>

                @foreach ($header_pbd as $route)
                    @php $route_total = 0; @endphp
                    @foreach ($route as $head)
                        @php
                            $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                            $qty = $order['qty'] ?? 0;
                            $route_total += $qty;
                            $total_pbd += $qty;
                        @endphp
                        <td class="{{ $qty ? 'rpt-qty-filled' : '' }}">{{ $qty }}</td>
                        <td>{{ $order['remark'] ?? '' }}</td>
                    @endforeach
                    <td class="rpt-route-total">{{ $route_total }}</td>
                @endforeach
                <td class="rpt-type-total-pbd">{{ $total_pbd }}</td>

                <td class="rpt-grand-total">{{ $total_normal + $total_special + $total_pbd }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
