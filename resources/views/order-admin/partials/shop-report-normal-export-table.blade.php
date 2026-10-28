<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
{{-- Full, unpaginated normal-routes report table consumed only by the
     "Export to Excel" JS on shop-report-morning-normal via fetch(); never
     meant to be viewed directly. --}}
<table id="exportTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Item Code | Item</th>
            @foreach ($header_normal as $head)
                <th>{{ $head->shop_name }} ({{ $head->branch_code }})</th>
            @endforeach
            <th>Normal Route Total</th>
        </tr>
    </thead>
    <tbody>
        @php $index = 0; @endphp
        @foreach ($products as $product)
            @php $total_normal = 0; @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                @foreach ($header_normal as $head)
                    @php
                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                        $words = $noteWords[$head->branch_code] ?? [];
                        $qty = $order['qty'] ?? 0;
                        $total_normal += $qty;
                    @endphp
                    <td>{{ $qty }} | {{ $words[$index] ?? '' }}</td>
                @endforeach

                <td>{{ $total_normal }}</td>
            </tr>
            @php $index++; @endphp
        @endforeach
    </tbody>
</table>
</body>
</html>
