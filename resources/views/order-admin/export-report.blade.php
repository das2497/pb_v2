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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>

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
                                                <small class="float-right text-danger">Default: All Routes</small>
                                                <select id="routeSelect" name="routes[]" class="form-control" multiple
                                                    size="5" onchange="getRoutes();">
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
                                                        <option value="{{$category->category}}" @if(in_array($category->category, $selectedCategories)) selected @endif>{{ $category->category }}
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
                                                        <select id="time_period" name="time_period[]"
                                                            class="form-control" multiple size="4" onchange="getTimePeriods();">
                                                            @foreach ($orderTimes as $time)
                                                            <option value="{{ $time }}" @if(in_array($time, $selectedTimePeriods)) selected @endif>
                                                                {{ $time }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="tip-text">
                                                            <strong>Tip:</strong> Hold Ctrl (Cmd on Mac) to select
                                                            multiple time periods
                                                        </div>
                                                        <div id="selectedTimePeriodsDisplay" class="selected-display mt-2">
                                                            <em>No time periods selected</em>
                                                        </div>
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
                                            <button class="btn btn-success" id="exportBtn" onclick="exportToExcel()">Export to Excel</button>
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
                                            Time Period: <strong>{{ implode(', ', array_map('ucfirst', $selectedTimePeriods)) }}</strong>
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
<script src="{{ asset('assets/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('assets/libs/js/main-js.js') }}"></script>
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

    // Show which routes/categories are currently selected
    function updateSelectedDisplay(selectId, displayId, emptyText) {
        const select = document.getElementById(selectId);
        const display = document.getElementById(displayId);
        const selected = Array.from(select.selectedOptions).map(o => o.text.trim());
        display.innerHTML = selected.length ? selected.join(', ') : `<em>${emptyText}</em>`;
    }

    function getRoutes() {
        updateSelectedDisplay('routeSelect', 'selectedRoutesDisplay', 'No routes selected');
    }

    function getCategories() {
        updateSelectedDisplay('categorySelect', 'selectedDisplay', 'No categories selected');
    }

    function getTimePeriods() {
        updateSelectedDisplay('time_period', 'selectedTimePeriodsDisplay', 'No time periods selected');
    }

    // Initial call so a reload after Search reflects what's actually selected
    getRoutes();
    getCategories();
    getTimePeriods();

    // Export to Excel with proper handling of remarks and column widths.
    // Colors the cells to match the on-screen Bootstrap classes already on
    // this table (table-info = route total, table-warning = grand total,
    // thead/tfoot = header/total-row tint) - same "rpt-*"-class idea used
    // on the Shop Report exports, just reading the classes this table
    // already has instead of adding new ones. Built with ExcelJS rather
    // than SheetJS: SheetJS's free/community build silently drops any cell
    // style (fill/font/border) on write - confirmed by round-tripping a
    // styled cell through it and finding the style gone - so this export's
    // old border/alignment styling never actually reached the downloaded
    // file either. ExcelJS's styles survive a write→read round-trip.
    async function exportToExcel() {
        const table = document.getElementById('ordersTable');
        if (!table) {
            alert('Table not found!');
            return;
        }

        const btn = document.getElementById('exportBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Exporting...';

        try {
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

            await downloadTableAsExcel(clonedTable);
        } catch (e) {
            alert('Export failed: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function downloadTableAsExcel(table) {
        const FILL_MAP = { info: 'D9EAD3', warning: 'EA9999', thead: 'FFF2CC', tfoot: 'D9EAD3' };
        const WHITE_TEXT = new Set(['warning']);
        const THIN_BORDER = { style: 'thin', color: { argb: 'FF999999' } };

        function classify(cls, inThead, inTfoot) {
            if (/\btable-warning\b/.test(cls)) return 'warning';
            if (/\btable-info\b/.test(cls)) return 'info';
            if (inThead) return 'thead';
            if (inTfoot) return 'tfoot';
            return null;
        }

        // Colspan/rowspan-aware walk of the table into a plain grid - this
        // table's header uses both (route-group cells span multiple shop
        // columns, and #/Item Code/Item/Route Total/Grand Total span both
        // header rows), so a rowspan'd cell's class/fill needs to carry
        // down into the row(s) below it too.
        function buildGrid(tbl) {
            const grid = [];
            Array.from(tbl.rows).forEach((row, r) => {
                grid[r] = grid[r] || [];
                let c = 0;
                const inThead = !!row.closest('thead');
                const inTfoot = !!row.closest('tfoot');
                Array.from(row.cells).forEach((cell) => {
                    while (grid[r][c] !== undefined) c++;
                    const colSpan = cell.colSpan || 1;
                    const rowSpan = cell.rowSpan || 1;
                    const className = cell.className || '';
                    const text = cell.textContent.replace(/\s+/g, ' ').trim();
                    for (let dr = 0; dr < rowSpan; dr++) {
                        grid[r + dr] = grid[r + dr] || [];
                        for (let dc = 0; dc < colSpan; dc++) {
                            grid[r + dr][c + dc] = {
                                value: (dr === 0 && dc === 0) ? text : '',
                                className,
                                inThead,
                                inTfoot,
                            };
                        }
                    }
                    c += colSpan;
                });
            });
            return grid;
        }

        const grid = buildGrid(table);
        const rowCount = grid.length;
        const colCount = grid.reduce((max, row) => Math.max(max, row.length), 0);

        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('Orders');
        const colWidths = new Array(colCount).fill(10);

        for (let r = 0; r < rowCount; r++) {
            for (let c = 0; c < colCount; c++) {
                const info = grid[r] && grid[r][c];
                const value = info ? info.value : '';
                const role = info ? classify(info.className, info.inThead, info.inTfoot) : null;

                const xlsxCell = ws.getCell(r + 1, c + 1);
                // Kept as plain text throughout (never Number(value)): an
                // item code like "0001" would lose its leading zero if
                // converted to a number.
                xlsxCell.value = value;
                xlsxCell.alignment = { horizontal: 'center', vertical: 'middle' };
                xlsxCell.border = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER };

                if (role) {
                    xlsxCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + FILL_MAP[role] } };
                    xlsxCell.font = { bold: true, color: { argb: WHITE_TEXT.has(role) ? 'FFFFFFFF' : 'FF000000' } };
                }

                colWidths[c] = Math.max(colWidths[c], Math.min(value.length + 4, 50));
            }
        }

        colWidths.forEach((w, i) => { ws.getColumn(i + 1).width = w; });

        // Generate safe filename
        @php
            $jsRoutes = is_array($route) ? $route : [$route];
            $jsState = $state;
            $jsFromDate = $fromDate;
            $jsToDate = $toDate;
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

        const buffer = await wb.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }
</script>
</body>
</html>