@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Rep | Export Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rep-order-theme.css') }}">
<style>
    /* This page's filter inputs stay square-cornered - override the
       shared pill-shaped .rep-order-filters-bar styling (used elsewhere,
       e.g. create-order) just for #report-filters. */
    #report-filters select.form-control,
    #report-filters input.form-control {
        border-radius: 0.375rem;
    }

    .table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    #report-table-container table {
        border-collapse: collapse;
        min-width: 100%;
        width: max-content;
    }

    #report-table-container th,
    #report-table-container td {
        border: 1px solid black;
        padding: 12px 20px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        text-transform: capitalize;
    }

    #report-table-container thead th {
        background: #ffffff;
        position: sticky;
        z-index: 5;
    }

    #report-table-container th:nth-child(1), #report-table-container td:nth-child(1),
    #report-table-container th:nth-child(2), #report-table-container td:nth-child(2),
    #report-table-container th:nth-child(3), #report-table-container td:nth-child(3) {
        position: sticky;
        z-index: 4;
    }

    #report-table-container th:nth-child(1), #report-table-container td:nth-child(1) {
        left: 0;
        min-width: 60px;
        width: 60px;
    }

    #report-table-container th:nth-child(2), #report-table-container td:nth-child(2) {
        left: 60px;
        min-width: 120px;
    }

    #report-table-container th:nth-child(3), #report-table-container td:nth-child(3) {
        left: 180px;
        min-width: 280px;
    }

    #report-table-container tbody td:nth-child(1),
    #report-table-container tbody td:nth-child(2),
    #report-table-container tbody td:nth-child(3) {
        background-color: var(--rep-order-bg-soft);
    }

    /* Route / total column tints, consistent with the rep blue palette
       instead of Bootstrap's default red/green/yellow table helpers. */
    .report-col-route {
        background-color: var(--rep-order-bg-soft) !important;
    }

    .report-col-route-total {
        background-color: #e3edf3 !important;
        color: var(--rep-order-primary-dark);
    }

    .report-col-grand-total {
        background-color: #fcefe0 !important;
        color: var(--rep-order-accent);
    }

    tfoot .report-col-route-total,
    tfoot .report-col-grand-total {
        background-color: #e3edf3 !important;
    }

    tfoot .report-col-grand-total {
        background-color: #fcefe0 !important;
    }

    .report-zero {
        color: #b9c4ca;
    }

    #report-table-container tbody tr:hover td {
        background-color: #eef5fa;
    }

    /* Grand Total row stays visible while scrolling a long product list. */
    #report-table-container tfoot td {
        position: sticky;
        bottom: 0;
        z-index: 3;
    }

    .rep-report-loading {
        text-align: center;
        padding: 56px 20px;
        color: #6b8694;
    }
</style>
@endpush

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Export Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Export Report</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader  -->
                <!-- ============================================================== -->
                <div class="ecommerce-widget">
                    <div class="row">
                        <div class="col-12">
                            <div class="card rep-order-card">
                                <h5 class="card-header">Export Report</h5>

                                <div class="col-12">
                                    <form id="report-filters" class="row g-2 p-2 rep-order-filters-bar" method="GET" action="{{ route('rep.export-report') }}">
                                        <div class="col-12 col-lg-4 mb-3">
                                            <label for="routeSelect" class="rep-order-form-label"><i class="fa fa-route"></i> Routes <small class="float-right text-muted">Default: All Routes</small></label>
                                            <select id="routeSelect" name="routes[]" class="form-control" multiple size="5">
                                                @foreach ($routes as $routeItem)
                                                <option value="{{ $routeItem->name }}" @selected(in_array($routeItem->name, $selectedRoutes, true))>
                                                    {{ $routeItem->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple routes.</div>
                                            <div id="selectedRoutesDisplay" class="mt-1"><em>No routes selected</em></div>
                                        </div>
                                        <div class="col-12 col-lg-4 mb-3">
                                            <label for="categorySelect" class="rep-order-form-label"><i class="fa fa-tags"></i> Categories <small class="float-right text-muted">Default: none</small></label>
                                            <select name="categories[]" class="form-control" id="categorySelect" multiple size="5">
                                                @foreach ($categories as $cat)
                                                <option value="{{ $cat->category }}" @selected(in_array($cat->category, $selectedCategories, true))>{{ $cat->category }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple categories.</div>
                                            <div id="selectedCategoriesDisplay" class="mt-1"><em>No categories selected</em></div>
                                        </div>
                                        <div class="col-12 col-lg-4 mb-3">
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <label for="startDate" class="rep-order-form-label"><i class="fa fa-calendar"></i> Start Date</label>
                                                    <input type="date" class="form-control" id="startDate" name="start_date" value="{{ $fromDate }}">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="endDate" class="rep-order-form-label"><i class="fa fa-calendar"></i> End Date</label>
                                                    <input type="date" class="form-control" id="endDate" name="end_date" value="{{ $toDate }}">
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <label for="time_period" class="rep-order-form-label"><i class="fa fa-clock"></i> Time Period</label>
                                                    <select id="time_period" name="time_period[]" class="form-control" multiple size="4">
                                                        @foreach ($orderTimes as $time)
                                                        <option value="{{ $time }}" @selected(in_array($time, $selectedTimePeriods, true))>{{ $time }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple time periods.</div>
                                                    <div id="selectedTimePeriodsDisplay" class="mt-1"><em>No time periods selected</em></div>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <label for="state" class="rep-order-form-label"><i class="fa fa-flag"></i> State</label>
                                                    <select id="state" name="state" class="form-control">
                                                        <option value="Pending" @selected($state === 'Pending')>Pending</option>
                                                        <option value="Processing" @selected($state === 'Processing')>Processing</option>
                                                        <option value="Complete" @selected($state === 'Complete')>Complete</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-4 d-flex align-items-end mb-3">
                                            <button type="submit" class="btn btn-outline-primary w-100"><i class="fa fa-search"></i> Search</button>
                                        </div>
                                    </form>

                                    <div class="row border-top p-2 m-0 align-items-center">
                                        <div class="col-12 col-lg-6">
                                            <button class="btn btn-outline-success" id="exportBtn" onclick="exportToExcel()"><i class="fa fa-file-excel"></i> Export to Excel</button>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="row">
                                                <div class="col-6 d-flex justify-content-end align-items-center">
                                                    <label class="rep-order-form-label mb-0">Show Remark</label>
                                                </div>
                                                <div class="col-6">
                                                    <label class="rep-order-switch">
                                                        <input type="checkbox" id="remarkToggle" checked>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3 mt-2">
                                        <h4 class="text-center font-weight-bold mb-1" id="report-summary-routes-date"></h4>
                                        <p class="text-center text-muted mb-0" id="report-summary-details"></p>
                                    </div>
                                </div>

                                <div class="card-body p-0" id="report-table-container">
                                    <div class="rep-report-loading">Loading report&hellip;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    (function () {
        var form = document.getElementById('report-filters');
        var container = document.getElementById('report-table-container');
        var dataUrl = '{{ route('rep.export-report-data') }}';

        function updateSelectedDisplay(selectId, displayId, emptyText) {
            var select = document.getElementById(selectId);
            var display = document.getElementById(displayId);
            var selected = Array.from(select.selectedOptions).map(function (o) { return o.text.trim(); });
            display.innerHTML = selected.length ? selected.join(', ') : '<em>' + emptyText + '</em>';
        }

        function refreshSelectedDisplays() {
            updateSelectedDisplay('routeSelect', 'selectedRoutesDisplay', 'No routes selected');
            updateSelectedDisplay('categorySelect', 'selectedCategoriesDisplay', 'No categories selected');
            updateSelectedDisplay('time_period', 'selectedTimePeriodsDisplay', 'No time periods selected');
        }

        function rmkManage() {
            var checked = document.getElementById('remarkToggle').checked;
            var rmkCells = container.querySelectorAll('.report-col-rmk');
            var qtyCells = container.querySelectorAll('.report-col-qty');

            rmkCells.forEach(function (cell) { cell.style.display = checked ? '' : 'none'; });
            qtyCells.forEach(function (cell) { cell.colSpan = checked ? 1 : 2; });
        }

        // The thead has 3 stacked rows; sticky "top" only stacks them
        // correctly on scroll if each row's offset accounts for the real
        // (rendered) height of the rows above it - a flat top:0 on every
        // row makes them pile on top of one another (and hide the first
        // body row) once scrolled. Measured after each table swap since
        // row heights depend on the actual shop/route counts returned.
        function applyStickyOffsets() {
            var rows = container.querySelectorAll('#ordersTable thead tr');
            var offset = 0;
            rows.forEach(function (row) {
                Array.from(row.children).forEach(function (cell) {
                    cell.style.top = offset + 'px';
                });
                offset += row.offsetHeight;
            });
        }

        function formatDate(value) {
            try {
                return new Date(value + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            } catch (e) {
                return value;
            }
        }

        function updateSummary(params) {
            var routes = params.getAll('routes[]');
            var categories = params.getAll('categories[]');
            var from = params.get('start_date');
            var to = params.get('end_date');

            var routeText = 'All Routes';
            if (routes.length === 1) routeText = routes[0];
            else if (routes.length > 1) routeText = routes.join(', ') + ' (' + routes.length + ' routes)';

            var dateText = from === to
                ? formatDate(from) + ' (00:00 - 23:59)'
                : formatDate(from) + ' 00:00 - ' + formatDate(to) + ' 23:59';

            document.getElementById('report-summary-routes-date').textContent = routeText + '   ' + dateText;

            var timePeriods = params.getAll('time_period[]');
            var details = 'Time Period: ' + (timePeriods.length ? timePeriods.join(', ') : 'Morning') + '  |  Status: ' + params.get('state');
            if (categories.length) {
                details += '  |  Categories: ' + categories.join(', ');
            }
            document.getElementById('report-summary-details').textContent = details;
        }

        function buildParams() {
            return new URLSearchParams(new FormData(form));
        }

        function loadReport(pushState) {
            var params = buildParams();
            updateSummary(params);

            if (pushState) {
                history.pushState(null, '', '{{ url('/rep/export-report') }}?' + params.toString());
            }

            container.innerHTML = '<div class="rep-report-loading">Loading report&hellip;</div>';

            fetch(dataUrl + '?' + params.toString())
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    container.innerHTML = html;
                    rmkManage();
                    applyStickyOffsets();
                })
                .catch(function () {
                    container.innerHTML = '<div class="rep-report-loading">Could not load the report. Please try again.</div>';
                });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            loadReport(true);
        });

        document.getElementById('remarkToggle').addEventListener('change', rmkManage);
        document.getElementById('routeSelect').addEventListener('change', refreshSelectedDisplays);
        document.getElementById('categorySelect').addEventListener('change', refreshSelectedDisplays);
        document.getElementById('time_period').addEventListener('change', refreshSelectedDisplays);

        refreshSelectedDisplays();
        loadReport(false);

        window.exportToExcel = function () {
            var table = document.getElementById('ordersTable');
            if (!table) {
                alert('Nothing to export yet - run a search first.');
                return;
            }

            var clonedTable = table.cloneNode(true);
            var isRemarkVisible = document.getElementById('remarkToggle').checked;

            var rmkCells = clonedTable.querySelectorAll('.report-col-rmk');
            var qtyCells = clonedTable.querySelectorAll('.report-col-qty');

            if (!isRemarkVisible) {
                rmkCells.forEach(function (cell) {
                    if (cell.parentNode) cell.parentNode.removeChild(cell);
                });
                qtyCells.forEach(function (cell) { cell.colSpan = 2; });
            } else {
                qtyCells.forEach(function (cell) { cell.colSpan = 1; });
            }

            var wb = XLSX.utils.table_to_book(clonedTable, { sheet: 'Orders' });
            var ws = wb.Sheets['Orders'];
            var range = XLSX.utils.decode_range(ws['!ref']);

            var borderStyle = {
                top: { style: 'thin' }, bottom: { style: 'thin' },
                left: { style: 'thin' }, right: { style: 'thin' }
            };

            for (var R = range.s.r; R <= range.e.r; ++R) {
                for (var C = range.s.c; C <= range.e.c; ++C) {
                    var addr = XLSX.utils.encode_cell({ r: R, c: C });
                    if (!ws[addr]) ws[addr] = { v: '' };
                    if (!ws[addr].s) ws[addr].s = {};
                    ws[addr].s.border = borderStyle;
                    ws[addr].s.alignment = { horizontal: 'center', vertical: 'center' };
                }
            }

            var colWidths = [];
            for (var C2 = range.s.c; C2 <= range.e.c; ++C2) {
                var maxWidth = 10;
                for (var R2 = range.s.r; R2 <= range.e.r; ++R2) {
                    var cell = ws[XLSX.utils.encode_cell({ r: R2, c: C2 })];
                    if (cell && cell.v !== undefined) {
                        var len = cell.v.toString().length;
                        if (len > maxWidth) maxWidth = len;
                    }
                }
                colWidths.push({ wch: Math.min(maxWidth + 4, 50) });
            }
            ws['!cols'] = colWidths;

            var params = buildParams();
            var routes = params.getAll('routes[]');
            var routePart = 'All_Routes';
            if (routes.length === 1) routePart = routes[0].replace(/[^a-zA-Z0-9]/g, '_');
            else if (routes.length > 1) routePart = routes.length + '_Routes';

            var from = (params.get('start_date') || '').replace(/-/g, '');
            var to = (params.get('end_date') || '').replace(/-/g, '');
            var status = params.get('state') || '';

            var filename = routePart + '_' + status + '_Orders_' + from + '_to_' + to + '.xlsx';

            XLSX.writeFile(wb, filename);
        };
    })();
</script>
@endpush
