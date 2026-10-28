<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
{{-- Full, unpaginated report table consumed only by the "Export to Excel"
     JS on shop-report-morning / shop-report-evening via fetch(); never
     meant to be viewed directly. Shares the normal/special/pbd + qty/remark
     layout of those pages, just without pagination. --}}
<table id="exportTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Item Code | Item</th>

            @foreach ($header_normal as $head)
                <th colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
            @endforeach
            <th>Normal Route Total</th>

            @foreach ($header_special as $head)
                <th colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
            @endforeach
            <th>Special Route Total</th>

            @foreach ($header_pbd as $head)
                <th colspan="2">{{ $head->shop_name }} ({{ $head->branch_code }})</th>
            @endforeach
            <th>PBD Route Total</th>

            <th>Total</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            @foreach ($header_normal as $head)
                <th>Qty</th>
                <th>Remark</th>
            @endforeach
            <th></th>
            @foreach ($header_special as $head)
                <th>Qty</th>
                <th>Remark</th>
            @endforeach
            <th></th>
            @foreach ($header_pbd as $head)
                <th>Qty</th>
                <th>Remark</th>
            @endforeach
            <th></th>
            <th></th>
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

                @foreach ($header_normal as $head)
                    @php
                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                        $qty = $order['qty'] ?? 0;
                        $total_normal += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                    <td>{{ $order['remark'] ?? '' }}</td>
                @endforeach
                <td>{{ $total_normal }}</td>

                @foreach ($header_special as $head)
                    @php
                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                        $qty = $order['qty'] ?? 0;
                        $total_special += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                    <td>{{ $order['remark'] ?? '' }}</td>
                @endforeach
                <td>{{ $total_special }}</td>

                @foreach ($header_pbd as $head)
                    @php
                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                        $qty = $order['qty'] ?? 0;
                        $total_pbd += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                    <td>{{ $order['remark'] ?? '' }}</td>
                @endforeach
                <td>{{ $total_pbd }}</td>

                <td>{{ $total_normal + $total_special + $total_pbd }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
