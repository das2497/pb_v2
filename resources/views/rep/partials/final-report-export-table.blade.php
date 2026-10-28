<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
{{-- Full, unpaginated final-report table consumed only by the "Export to
     Excel" JS on final-report-morning / final-report-evening via fetch();
     never meant to be viewed directly. Route-grouped (normal/special/pbd),
     one qty column per route — unlike the shop report tables, there's no
     separate remark column here. --}}
<table id="exportTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Item Code | Item</th>
            @foreach ($routes_normal as $route)
                <th>{{ $route->name }}</th>
            @endforeach
            <th>Normal Route Total</th>
            @foreach ($routes_special as $route)
                <th>{{ $route->name }}</th>
            @endforeach
            <th>Special Route Total</th>
            @foreach ($routes_pbd as $route)
                <th>{{ $route->name }}</th>
            @endforeach
            <th>PBD Total</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            @php
                $normal_total = 0;
                $special_total = 0;
                $pbd_total = 0;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                @foreach ($routes_normal as $route)
                    @php
                        $qty = $orderMap[$route->name][$product->item_number] ?? 0;
                        $normal_total += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                @endforeach
                <td>{{ $normal_total }}</td>

                @foreach ($routes_special as $route)
                    @php
                        $qty = $orderMap[$route->name][$product->item_number] ?? 0;
                        $special_total += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                @endforeach
                <td>{{ $special_total }}</td>

                @foreach ($routes_pbd as $route)
                    @php
                        $qty = $orderMap[$route->name][$product->item_number] ?? 0;
                        $pbd_total += $qty;
                    @endphp
                    <td>{{ $qty }}</td>
                @endforeach
                <td>{{ $pbd_total }}</td>

                <td>{{ $normal_total + $special_total + $pbd_total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
