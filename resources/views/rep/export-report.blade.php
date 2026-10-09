<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REP | Export Report</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/chartist-bundle/chartist.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/material-design-iconic-font/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icon-css/flag-icon.min.css') }}">

    <!-- Add the SheetJS library (you can include it via CDN or download it) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .div {
            /* height: 400px; */
            /* max-width: 100vw; */
            overflow-x: auto;
            overflow-y: auto;
            position: relative;
            /* margin-top: 100px; */
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            th {
                position: sticky;
                top: 0;

                &:first-child {
                    z-index: 3;
                }
            }
        }

        th,
        td {
            /* padding: 10px 100px; */
            text-transform: capitalize;
            border: 1px solid black;
            padding-inline: 20px;
        }

        th {
            background: white;
            /* color: white; */
            white-space: nowrap;
        }

        th:first-child,
        td:first-child {
            position: sticky;
            left: 0px;
            z-index: 3;
            background-color: #feebe9;
            color: #000;
            width: 50px;
            /* Adjust this width for the first column (#) as needed */
            min-width: 50px;
        }

        th:nth-child(2),
        td:nth-child(2) {
            position: sticky;
            left: 50px;
            /* This should match the width of the first column */
            z-index: 3;
            background-color: #feebe9;
            color: #000;
            width: 100px;
            /* Adjust this width for the second column (Item Code | Item) as needed */
            min-width: 100px;
        }

        th:nth-child(3),
        td:nth-child(3) {
            position: sticky;
            left: 50px;
            /* This should match the width of the first column */
            z-index: 3;
            background-color: #feebe9;
            color: #000;
            width: 280px;
            /* Adjust this width for the second column (Item Code | Item) as needed */
            min-width: 280px;
        }

        /* ------------------------------------------------------------------------- */

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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }
    </style>
</head>

<body>

    <!-- header  -->
    @include('rep.components.header')
    <!-- /header  -->

    <!-- menu -->
    @include('rep.components.menu')
    <!-- /menu -->

    <!-- content -->
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Export Shop Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Export Shop Report</li>
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
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-12">
                            <div class="card">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="card-header">Export Shop Report</h5>
                                    </div>
                                    <div class="col-12">
                                        <form class="row border p-2 m-2" method="GET" action="/rep/export-report">
                                            @csrf
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="startDate" class="form-label">Start Date </label><small class="float-right text-danger">Default Today</small>
                                                <input type="date" class="form-control" id="startDate" name="start_date" value="{{ $fromDate }}">
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="endDate" class="form-label">End Date</label><small class="float-right text-danger">Default Today</small>
                                                <input type="date" class="form-control" id="endDate" name="end_date" value="{{ $toDate }}">
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="routeSelect" class="form-label">Route</label><small class="float-right text-danger">Default Route 1</small>
                                                <select id="routeSelect" name="route" class="form-control">
                                                    @foreach ($routes as $routeItem)
                                                    <option value="{{ $routeItem->name }}" @selected($route==$routeItem->name)>{{ $routeItem->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <label for="categorySelect" class="form-label">Categories</label><small class="float-right text-danger">Default none</small>
                                                <select name="categories[]" class="form-control" id="categorySelect" multiple size="5" onchange="getCategories();">
                                                    @foreach ($categories as $category)
                                                    <option value="{{$category->category}}" )>{{ $category->category }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="tip-text">
                                                    <strong>Tip:</strong> Hold Ctrl (Cmd on Mac) to select multiple categories
                                                </div>
                                                <div id="selectedDisplay" class="selected-display">
                                                    <em>No categories selected</em>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label for="time_period">Time period</label><small class="float-right text-danger">Default Morning</small>
                                                        <select id="time_period" name="time_period" class="form-control">
                                                            <option value="Morning" @selected($timePeriod=='Morning' )>Morning</option>
                                                            <option value="Evening" @selected($timePeriod=='Evening' )>Evening</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 pt-2">
                                                        <label for="state">State</label><small class="float-right text-danger">Default Pending</small>
                                                        <select id="state" name="state" class="form-control">
                                                            <option value="Pending" @selected($state=='Pending' )>Pending</option>
                                                            <option value="Processing" @selected($state=='Processing' )>Processing</option>
                                                            <option value="Complete" @selected($state=='Complete' )>Complete</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-4 mb-3">
                                                <button class="btn btn-primary w-100 submit-btn" type="submit">Search</button>
                                            </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-12">
                                                <!-- Add a button for export -->
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
                                                                    <input type="checkbox" name="access" onchange="rmkManege();" checked>
                                                                    <span class="slider"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <h4 class="text-center">{{ $route }} &nbsp;&nbsp;&nbsp; {{ $fromDate }} 00:00 - {{ $toDate }} 23:59</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="div">
                                        @php
                                        $reportDate = \Carbon\Carbon::now()->format('Y-m-d');
                                        @endphp
                                        @php
                                        $columnTotals = [];
                                        $grandTotal = 0;
                                        @endphp
                                        <table class="table table-bordered border-dark m-2" id="ordersTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 100px;">Item Code</th>
                                                    <th style="min-width: 280px;">Item</th>

                                                    @foreach ($shops as $shop)
                                                    <th colspan="2" class="text-center">
                                                        <strong>{{ $shop->shop_name }}</strong><br>
                                                        <small>{{ $shop->branch_code }}</small>
                                                    </th>
                                                    @endforeach

                                                    <th class="table-warning text-center">Total</th>
                                                </tr>

                                                <tr class="table-secondary">
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    @foreach ($shops as $shop)
                                                    <th class="text-center" id="qty">Qty</th>
                                                    <th class="text-center" id="rmk">Remark</th>
                                                    @endforeach
                                                    <th></th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach ($products as $product)
                                                @php
                                                $rowTotal = 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><strong>{{ $product->item_number }}</strong></td>
                                                    <td>{{ $product->name_sinhala }}</td>

                                                    @foreach ($shops as $shop)
                                                    @php
                                                    $code = trim($shop->branch_code);
                                                    $qty = $aggregatedOrders[$code][$product->item_number]['qty'] ?? 0;
                                                    $remark = $aggregatedOrders[$code][$product->item_number]['remark'] ?? '';

                                                    $rowTotal += $qty;

                                                    // ✅ Column total per shop
                                                    $columnTotals[$code] = ($columnTotals[$code] ?? 0) + $qty;
                                                    @endphp

                                                    <td class="text-center" id="qty">{{ $qty }}</td>
                                                    <td id="rmk">{{ $remark }}</td>
                                                    @endforeach

                                                    @php $grandTotal += $rowTotal; @endphp
                                                    <td class="table-warning text-center fw-bold">{{ $rowTotal }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>

                                            {{-- ✅ PERFECTLY MATCHED FOOTER --}}
                                            <tfoot class="table-success fw-bold">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-end">Grand Total</td>

                                                    @foreach ($shops as $shop)
                                                    @php $code = trim($shop->branch_code); @endphp
                                                    <td class="text-center" colspan="2" id="qty">{{ $columnTotals[$code] ?? 0 }}</td>
                                                    <td id="rmk"></td>
                                                    @endforeach

                                                    <td class="table-warning text-center">{{ $grandTotal }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="p-2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================== -->
                        <!-- end recent orders  -->

                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /content -->

    <!-- jQuery 3.3.1 -->
    <script src="{{ asset('assets/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <!-- Bootstrap bundle JS -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <!-- SlimScroll JS -->
    <script src="{{ asset('assets/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/libs/js/main-js.js') }}"></script>
    <!-- Chartist JS -->
    <script src="{{ asset('assets/vendor/charts/chartist-bundle/chartist.min.js') }}"></script>
    <!-- Sparkline JS -->
    <script src="{{ asset('assets/vendor/charts/sparkline/jquery.sparkline.js') }}"></script>
    <!-- Morris JS -->
    <script src="{{ asset('assets/vendor/charts/morris-bundle/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/charts/morris-bundle/morris.js') }}"></script>
    <!-- C3 Charts JS -->
    <script src="{{ asset('assets/vendor/charts/c3charts/c3.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/charts/c3charts/d3-5.4.0.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/charts/c3charts/C3chartjs.js') }}"></script>
    <!-- Dashboard E-commerce JS -->
    <script src="{{ asset('assets/libs/js/dashboard-ecommerce.js') }}"></script>
    <!-- Chart Bundle JS -->
    <script src="{{ asset('assets/vendor/charts/charts-bundle/Chart.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendor/charts/charts-bundle/chartjs.js') }}"></script>

    <script>
        function getCategories() {
            const selectBox = document.getElementById('categorySelect');
            const selectedValues = Array.from(selectBox.selectedOptions).map(option => option.value);

            console.log("Selected values array:", selectedValues);
            console.log("Joined string:", selectedValues.join(', '));

            // Display selected categories
            const displayElement = document.getElementById('selectedDisplay');
            if (selectedValues.length === 0) {
                displayElement.innerHTML = '<em>No categories selected</em>';
            } else if (selectedValues.length === 1) {
                displayElement.textContent = `Selected: ${selectedValues[0]}`;
            } else {
                displayElement.textContent = `Selected: ${selectedValues.join(', ')} (${selectedValues.length} categories)`;
            }
        }

        function exportToExcel() {
            // Get the table element
            var table = document.getElementById('ordersTable');

            // Clone the table to avoid modifying the original
            var clonedTable = table.cloneNode(true);

            // Get checkbox state
            var checkBox = document.querySelector('input[name="access"]');

            // Select elements in the clone
            var rmks = clonedTable.querySelectorAll('#rmk');
            var qtys = clonedTable.querySelectorAll('#qty');

            if (checkBox.checked) {
                qtys.forEach(function(q) {
                    q.colSpan = 1;
                });
            } else {
                rmks.forEach(function(header) {
                    header.parentNode.removeChild(header);
                });
                qtys.forEach(function(q) {
                    q.colSpan = 2;
                });
            }

            // Create workbook and worksheet from table
            var wb = XLSX.utils.table_to_book(clonedTable, {
                sheet: "Sheet1"
            });
            var ws = wb.Sheets["Sheet1"];

            // Get the range of the used cells (e.g., A1:Z100)
            var range = XLSX.utils.decode_range(ws['!ref']);

            // Define border style (thin black border on all sides)
            var borderStyle = {
                top: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                bottom: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                left: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                right: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                }
            };

            // Loop through all cells in the used range
            for (var R = range.s.r; R <= range.e.r; ++R) {
                for (var C = range.s.c; C <= range.e.c; ++C) {
                    var cellAddress = XLSX.utils.encode_cell({
                        r: R,
                        c: C
                    });

                    // Create cell object if it doesn't exist
                    if (!ws[cellAddress]) ws[cellAddress] = {
                        v: ""
                    };

                    // Initialize style object if not exists
                    if (!ws[cellAddress].s) ws[cellAddress].s = {};

                    // Apply border
                    ws[cellAddress].s.border = borderStyle;

                    // Optional: center align text + add some padding feel
                    ws[cellAddress].s.alignment = {
                        horizontal: "center",
                        vertical: "center"
                    };
                }
            }

            // Optional: Auto-size columns (looks better)
            var colWidths = [];
            for (var C = range.s.c; C <= range.e.c; ++C) {
                var maxWidth = 10; // minimum width
                for (var R = range.s.r; R <= range.e.r; ++R) {
                    var cell = ws[XLSX.utils.encode_cell({
                        r: R,
                        c: C
                    })];
                    if (cell && cell.v) {
                        var len = cell.v.toString().length;
                        if (len > maxWidth) maxWidth = len;
                    }
                }
                colWidths.push({
                    wch: maxWidth + 2
                }); // +2 for padding
            }
            ws['!cols'] = colWidths;

            // Generate filename
            var filename = '{{ $route }}_{{ $state }}_Orders_from_{{ $fromDate }}_to_{{ $toDate }}.xlsx';

            // Export file
            XLSX.writeFile(wb, filename);
        }

        function rmkManage() { // Fixed typo in function name for clarity
            var checkBox = document.querySelector('input[name="access"]');
            var rmks = document.querySelectorAll('#rmk'); // Fixed variable name
            var qtys = document.querySelectorAll('#qty'); // Fixed variable name

            if (checkBox.checked) {
                rmks.forEach(function(el) {
                    el.style.display = '';
                });
                qtys.forEach(function(q) {
                    q.colSpan = 1;
                });
            } else {
                rmks.forEach(function(el) {
                    el.style.display = 'none';
                });
                qtys.forEach(function(q) {
                    q.colSpan = 2;
                });
            }
        }

        // Initial call
        rmkManage();

        // Add event listener to update live table on checkbox change
        var checkBox = document.querySelector('input[name="access"]');
        if (checkBox) {
            checkBox.addEventListener('change', rmkManage);
        }
    </script>

</body>

</html>