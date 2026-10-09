<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Export Report</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/chartist-bundle/chartist.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/material-design-iconic-font/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icon-css/flag-icon.min.css') }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
    .table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    table {
        border-collapse: collapse;
        min-width: 100%;
        width: max-content;
    }

    th, td {
        border: 1px solid black;
        padding: 12px 20px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        text-transform: capitalize;
    }

    /* Sticky header */
    thead th {
        background: #ffffff;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    /* ===== STICKY FIRST 3 COLUMNS (FIXED) ===== */
    th:nth-child(1), td:nth-child(1),
    th:nth-child(2), td:nth-child(2),
    th:nth-child(3), td:nth-child(3) {
        position: sticky;
        z-index: 4;
    }

    /* Column positions */
    th:nth-child(1), td:nth-child(1) {
        left: 0;
        min-width: 60px;
        width: 60px;
    }

    th:nth-child(2), td:nth-child(2) {
        left: 60px;
        min-width: 120px;
    }

    th:nth-child(3), td:nth-child(3) {
        left: 180px;
        min-width: 280px;
    }

    /* ✅ Apply pink ONLY to body rows (not header/footer) */
    tbody td:nth-child(1),
    tbody td:nth-child(2),
    tbody td:nth-child(3) {
        background-color: #feebe9;
    }

    /* ✅ Keep Bootstrap colors working */
    tfoot td {
        background-color: inherit !important;
    }

    .table-success td {
        background-color: #d4edda !important;
    }

    .table-info {
        background-color: #d1ecf1 !important;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    /* Route header */
    .route-header {
        background-color: #f8f9fa !important;
        font-weight: bold;
    }

    /* Hover effect (optional but nice) */
    tbody tr:hover td {
        background-color: #f1f5ff;
    }

    /* ===== SWITCH STYLE ===== */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>
</head>
<body>

@include('order-admin.components.header')
@include('order-admin.components.menu')

<div class="dashboard-wrapper">
    <div class="dashboard-ecommerce">
        <div class="container-fluid dashboard-content">
            <div class="row">
                <div class="col-xl-12">
                    <div class="page-header">
                        <h2 class="pageheader-title">Export Shop Report</h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Export Shop Report</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ecommerce-widget">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="row">
                                <div class="col-12"><h5 class="card-header">Export Shop Report</h5></div>
                                <div class="col-12">
                                    <!-- Filters Form -->
                                        <form class="row border p-2 m-2" method="GET"
                                            action="/order-admin/export-report">
                                            @csrf
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="routeSelect" class="form-label">Routes</label>
                                                <small class="float-right text-danger">Default: ROUTE 1</small>
                                                <select id="routeSelect" name="routes[]" class="form-control" multiple
                                                    size="5">
                                                    @foreach ($routes as $routeItem)
                                                        <option value="{{ $routeItem->name }}" @if(is_array($route) && in_array($routeItem->name, $route)) selected @endif>
                                                            {{ $routeItem->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="tip-text">
                                                    <strong>Tip:</strong> Hold Ctrl (Cmd on Mac) to select multiple
                                                    routes
                                                </div>
                                                <div id="selectedRoutesDisplay" class="selected-display mt-2">
                                                    <em>No routes selected</em>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="categorySelect" class="form-label">Categories</label><small
                                                    class="float-right text-danger">Default none</small>
                                                <select name="categories[]" class="form-control" id="categorySelect"
                                                    multiple size="5" onchange="getCategories();">
                                                    @foreach ($categories as $category)
                                                        <option value="{{$category->category}}" )>{{ $category->category }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="tip-text">
                                                    <strong>Tip:</strong> Hold Ctrl (Cmd on Mac) to select multiple
                                                    categories
                                                </div>
                                                <div id="selectedDisplay" class="selected-display">
                                                    <em>No categories selected</em>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label for="startDate" class="form-label">Start Date </label><small
                                                            class="float-right text-danger">Default Today</small>
                                                        <input type="date" class="form-control" id="startDate" name="start_date"
                                                            value="{{ $fromDate }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="endDate" class="form-label">End Date</label><small
                                                            class="float-right text-danger">Default Today</small>
                                                        <input type="date" class="form-control" id="endDate" name="end_date"
                                                            value="{{ $toDate }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label for="time_period">Time period</label><small
                                                            class="float-right text-danger">Default Morning</small>
                                                        <select id="time_period" name="time_period"
                                                            class="form-control">
                                                            <option value="Morning" @selected($timePeriod == 'Morning')>
                                                                Morning</option>
                                                            <option value="Evening" @selected($timePeriod == 'Evening')>
                                                                Evening</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 pt-2">
                                                        <label for="state">State</label><small
                                                            class="float-right text-danger">Default Pending</small>
                                                        <select id="state" name="state" class="form-control">
                                                            <option value="Pending" @selected($state == 'Pending')>
                                                                Pending</option>
                                                            <option value="Processing" @selected($state == 'Processing')>
                                                                Processing</option>
                                                            <option value="Complete" @selected($state == 'Complete')>
                                                                Complete</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <button class="btn btn-primary w-100 submit-btn"
                                                    type="submit">Search</button>
                                            </div>
                                        </form>

                                    <div class="row border p-2 m-2">
                                        <div class="col-12 col-lg-6">
                                            <button class="btn btn-success" onclick="exportToExcel()">Export to Excel</button>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="row">
                                                <div class="col-6 d-flex justify-content-end align-items-center">
                                                    <label class="form-label">Remark</label>
                                                </div>
                                                <div class="col-6">
                                                    <label class="switch">
                                                        <input type="checkbox" name="access" onchange="rmkManage();" checked>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-4">
                                        <h4 class="text-center font-weight-bold">
                                            <!-- Routes, Date, Time Period, Status Display (unchanged) -->
                                            <span>
                                                @if(is_array($route))
                                                    @if(count($route) === 0) All Routes
                                                    @elseif(count($route) === 1) {{ $route[0] }}
                                                    @else {{ implode(', ', $route) }} <small class="text-muted">({{ count($route) }} routes)</small>
                                                    @endif
                                                @else {{ $route }}
                                                @endif
                                            </span>
                                            &nbsp;&nbsp;&nbsp;
                                            <span>
                                                @if($fromDate === $toDate)
                                                    {{ \Carbon\Carbon::parse($fromDate)->format('F j, Y') }} <small class="text-muted">(00:00 - 23:59)</small>
                                                @else
                                                    {{ \Carbon\Carbon::parse($fromDate)->format('F j, Y') }} 00:00 -
                                                    {{ \Carbon\Carbon::parse($toDate)->format('F j, Y') }} 23:59
                                                @endif
                                            </span>
                                        </h4>
                                        <p class="text-center text-muted mb-0">
                                            Time Period: <strong>{{ ucfirst($timePeriod) }}</strong>
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            Status: <strong>{{ ucfirst($state) }}</strong>
                                            @if(!empty($selectedCategories))
                                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                                Categories: <strong>{{ is_array($selectedCategories) ? implode(', ', $selectedCategories) : $selectedCategories }}</strong>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-wrapper">
                                    <table class="table table-bordered border-dark m-2" id="ordersTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">#</th>
                                                <th rowspan="2" style="min-width: 100px;">Item Code</th>
                                                <th rowspan="2" style="min-width: 280px;">Item</th>

                                                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                                                    <th colspan="{{ $routeShops->count() * 2 }}" class="text-center bg-light">
                                                        <strong>{{ $routeName }}</strong>
                                                    </th>
                                                    <th rowspan="2" class="table-info text-center fw-bold">Route Total</th>
                                                @endforeach

                                                <th rowspan="2" class="table-warning text-center">Grand Total</th>
                                            </tr>

                                            <tr class="table-secondary">
                                                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                                                    @foreach ($routeShops as $shop)
                                                        <th class="text-center" id="qty">Qty</th>
                                                        <th class="text-center" id="rmk">Remark</th>
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
                                                $routeTotals = []; // route => total qty
                                            @endphp

                                            @foreach ($products as $product)
                                                @php
                                                    $rowTotal = 0;
                                                    $productRouteTotals = array_fill_keys($shopsGroupedByRoute->keys()->toArray(), 0);
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><strong>{{ $product->item_number }}</strong></td>
                                                    <td>{{ $product->name_sinhala }}</td>

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
                                                            <td class="text-center" id="qty">{{ $qty }}</td>
                                                            <td id="rmk">{{ $remark }}</td>
                                                        @endforeach
                                                        <td class="table-info text-center fw-bold">{{ $routeQtySum }}</td>                                                        
                                                    @endforeach

                                                    @php $grandTotal += $rowTotal; @endphp
                                                    <td class="table-warning text-center fw-bold">{{ $rowTotal }}</td>                                                    
                                                </tr>

                                                @php
                                                    foreach ($productRouteTotals as $route => $total) {
                                                        $routeTotals[$route] = ($routeTotals[$route] ?? 0) + $total;
                                                    }
                                                @endphp
                                            @endforeach
                                        </tbody>

                                        <tfoot class="table-success fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end">Grand Total</td>

                                                @foreach ($shopsGroupedByRoute as $routeName => $routeShops)
                                                    @foreach ($routeShops as $shop)
                                                        @php $code = trim($shop->branch_code); @endphp
                                                        <td colspan="2" class="text-center" id="qty">
                                                            {{ $columnTotals[$code] ?? 0 }}
                                                        </td>
                                                        <td id="rmk"></td>
                                                    @endforeach
                                                    <td class="table-info text-center">
                                                        {{ $routeTotals[$routeName] ?? 0 }}
                                                    </td>
                                                @endforeach

                                                <td class="table-warning text-center">{{ $grandTotal }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts (unchanged) -->
<script src="{{ asset('assets/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
<!-- ... other scripts ... -->

<script>
    // Toggle Remark columns visibility
    function rmkManage() {
        const checkBox = document.querySelector('input[name="access"]');
        const rmkCells = document.querySelectorAll('#rmk');
        const qtyCells = document.querySelectorAll('#qty');

        if (checkBox.checked) {
            // Show remarks
            rmkCells.forEach(cell => cell.style.display = '');
            qtyCells.forEach(cell => cell.colSpan = 1);
        } else {
            // Hide remarks
            rmkCells.forEach(cell => cell.style.display = 'none');
            qtyCells.forEach(cell => cell.colSpan = 2);
        }
    }

    // Initial call and event listener
    rmkManage();
    document.querySelector('input[name="access"]').addEventListener('change', rmkManage);

    // Export to Excel with proper handling of remarks and column widths
    function exportToExcel() {
        const table = document.getElementById('ordersTable');
        if (!table) {
            alert('Table not found!');
            return;
        }

        // Clone table for export
        const clonedTable = table.cloneNode(true);
        const checkBox = document.querySelector('input[name="access"]');
        const isRemarkVisible = checkBox.checked;

        // Get all remark and qty cells in cloned table
        const rmkCells = clonedTable.querySelectorAll('#rmk');
        const qtyCells = clonedTable.querySelectorAll('#qty');

        if (!isRemarkVisible) {
            // Completely remove remark columns (headers, body, footer)
            rmkCells.forEach(cell => {
                if (cell.parentNode) {
                    cell.parentNode.removeChild(cell);
                }
            });
            // Make Qty span 2 columns
            qtyCells.forEach(cell => cell.colSpan = 2);
        } else {
            // Ensure Qty spans only 1 column
            qtyCells.forEach(cell => cell.colSpan = 1);
        }

        // Generate workbook
        const wb = XLSX.utils.table_to_book(clonedTable, { sheet: "Orders" });
        const ws = wb.Sheets["Orders"];

        // Get range
        const range = XLSX.utils.decode_range(ws['!ref']);

        // Apply borders and center alignment to all cells
        const borderStyle = {
            top: { style: "thin" },
            bottom: { style: "thin" },
            left: { style: "thin" },
            right: { style: "thin" }
        };

        for (let R = range.s.r; R <= range.e.r; ++R) {
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
                if (!ws[cellAddress]) ws[cellAddress] = { v: "" };
                if (!ws[cellAddress].s) ws[cellAddress].s = {};

                ws[cellAddress].s.border = borderStyle;
                ws[cellAddress].s.alignment = {
                    horizontal: "center",
                    vertical: "center"
                };
            }
        }

        // Auto-size columns based on content length
        const colWidths = [];
        for (let C = range.s.c; C <= range.e.c; ++C) {
            let maxWidth = 10; // minimum width
            for (let R = range.s.r; R <= range.e.r; ++R) {
                const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
                if (cell && cell.v !== undefined) {
                    const text = cell.v.toString();
                    const len = text.length;
                    if (len > maxWidth) maxWidth = len;
                }
            }
            colWidths.push({ wch: Math.min(maxWidth + 4, 50) }); // cap at 50 chars
        }
        ws['!cols'] = colWidths;

        // Generate safe filename
        @php
            $jsRoutes = is_array($route) ? $route : [$route];
            $jsState = $state;
            $jsFromDate = $fromDate;
            $jsToDate = $toDate;
            $jsTimePeriod = $timePeriod ?? 'Morning';
        @endphp

        const routes = @json($jsRoutes);
        let routePart = "All_Routes";

        if (Array.isArray(routes) && routes.length > 0) {
            if (routes.length === 1) {
                routePart = routes[0].replace(/[^a-zA-Z0-9]/g, '_');
            } else {
                routePart = routes.length + "_Routes";
            }
        }

        const from = "{{ $jsFromDate }}".replace(/-/g, '');
        const to = "{{ $jsToDate }}".replace(/-/g, '');
        const status = "{{ $jsState }}";

        const filename = `${routePart}_${status}_Orders_${from}_to_${to}.xlsx`;

        // Download file
        XLSX.writeFile(wb, filename);
    }
</script>
</body>
</html>