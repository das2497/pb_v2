@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Cart')

@push('styles')
<style>
        .div {
            height: 400px;
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
                left: 0;


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

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 3;
                background-color: #feebe9;
                color: #000;
            }
        }

        td {

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 2;
                background-color: #feebe9;
                color: #000;
            }
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
                            <h2 class="pageheader-title"> Evening Shop Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Evening Shop Report</li>
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
                        <div class="col-12 col-lg-4 d-grid">
                            <a class="btn btn-outline-primary mb-4" href="/order-admin/shop-report">Morning Report</a>
                        </div>
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-12">
                            <div class="card">
                                <div class="row">
                                    <div class="col-12 col-lg-4">
                                        <h5 class="card-header">Today Evening Shop Report &nbsp;&nbsp;
                                            <a href="/order-admin/shop-report-full-screen-evening" type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#morning_shop_wise_report">
                                                Full Screen
                                            </a>
                                            <button type="button" id="exportBtn" class="btn btn-success" onclick="exportFullReport()">
                                                Export to Excel
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="col-12 col-lg-8 pt-3">
                                        <form class="row g-2" action="/order-admin/shop-report-evening" method="GET">
                                            @csrf
                                            <div class="col-12 col-sm-4">
                                                <input type="date" name="date" id="" class="form-control" value="{{ $currentDate }}">
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <select name="state" id="" class="form-control">
                                                    <option value="Processing" @selected($state === 'Processing')>Processing</option>
                                                    <option value="Pending" @selected($state === 'Pending')>Pending</option>
                                                    <option value="Complete" @selected($state === 'Complete')>Complete</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <button class="btn btn-primary" type="submit">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="div">
                                        <table class="table-bordered border-dark">
                                            <thead class="bg-light ">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 280px;">Item Code | Item</th>
                                                    @foreach ($header_normal as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-secondary">
                                                        <h6>{{ $route->first()->route_name }} Total</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-primary">
                                                        <h6>Normal Route Total</h6>
                                                    </th>

                                                    @foreach ($header_special as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-secondary">
                                                        <h6>{{ $route->first()->route_name }} Total</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-danger">
                                                        <h6>Special Route Total</h6>
                                                    </th>

                                                    @foreach ($header_pbd as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-secondary">
                                                        <h6>{{ $route->first()->route_name }} Total</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-danger">
                                                        <h6>PBD Route Total</h6>
                                                    </th>

                                                    <th class="table-warning">
                                                        <h6>Total</h6>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody style="max-height: 400px;">
                                                @php
                                                $total_normal = 0;
                                                $total_special = 0;
                                                $total_pbd = 0;
                                                $index = 0;
                                                @endphp
                                                @foreach ($products as $product)
                                                @php
                                                // Reset the totals for each product
                                                $total_normal = 0;
                                                $total_special = 0;
                                                $total_pbd = 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                                                    {{-- Normal Route --}}
                                                    @foreach ($header_normal as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $orderQty = $order['qty'] ?? 0;
                                                    $route_total += $orderQty;
                                                    $total_normal += $orderQty;
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order['remark'] ?? '' }}</span>
                                                    </th>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach

                                                    <td class="table-primary">{{ $total_normal }}</td>

                                                    {{-- Special Route --}}
                                                    @foreach ($header_special as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $orderQty = $order['qty'] ?? 0;
                                                    $route_total += $orderQty;
                                                    $total_special += $orderQty;
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order['remark'] ?? '' }}</span>
                                                    </th>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach

                                                    <td class="table-danger">{{ $total_special }}</td>

                                                    {{-- PBD Route --}}
                                                    @foreach ($header_pbd as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $orderQty = $order['qty'] ?? 0;
                                                    $route_total += $orderQty;
                                                    $total_pbd += $orderQty;
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order['remark'] ?? '' }}</span>
                                                    </th>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach

                                                    <td class="table-danger">{{ $total_pbd }}</td>

                                                    {{-- Total for all routes --}}
                                                    <td class="table-warning">{{ $total_normal + $total_special + $total_pbd }}</td>
                                                </tr>
                                                @php
                                                $index++;
                                                @endphp
                                                @endforeach
                                            </tbody>

                                        </table>
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
    
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }

        async function exportFullReport() {
            const btn = document.getElementById('exportBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Exporting...';

            try {
                const params = new URLSearchParams(window.location.search);
                const res = await fetch('/order-admin/shop-report-evening/export?' + params.toString());
                if (!res.ok) throw new Error('Export request failed');

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const table = doc.getElementById('exportTable');
                if (!table) throw new Error('Export table not found');

                await downloadTableAsExcel(table, 'Evening_Shop_Report');
            } catch (e) {
                alert('Export failed: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Colors the exported cells to match the report's on-screen look
        // (header/route-total/type-total/grand-total fills) - the "rpt-*"
        // classes are set on the export partial's <th>/<td> cells purely
        // for this, and mean nothing on screen. Switched from SheetJS
        // (xlsx.full.min.js) to ExcelJS here: SheetJS's free/community
        // build silently drops any cell style (fill/font/border) on write -
        // confirmed by round-tripping a styled cell through it and finding
        // the style gone - so the old code's border/alignment styling never
        // actually reached the downloaded file either. ExcelJS's styles
        // survive a write→read round-trip.
        async function downloadTableAsExcel(table, filenamePrefix) {
            const FILL_MAP = {
                'rpt-header': 'FFF2CC',
                'rpt-qty-filled': 'D9EAD3',
                'rpt-route-total': 'D9EAD3',
                'rpt-type-total-normal': 'B6D7A8',
                'rpt-type-total-special': 'CFE2F3',
                'rpt-type-total-pbd': 'FCE5CD',
                'rpt-grand-total': 'EA9999',
            };
            const WHITE_TEXT_CLASSES = new Set(['rpt-grand-total']);
            const THIN_BORDER = { style: 'thin', color: { argb: 'FF999999' } };

            // Resolves the table's cells (value + class) into a plain,
            // colspan-aware grid - e.g. a <th colspan="2"> only occupies
            // its own first column with text; the column(s) it spans over
            // still carry its class (so the fill color continues across
            // them) but with an empty value, mirroring how the on-screen
            // table reads visually without actually merging cells (the
            // source report file this mirrors has no merged cells either).
            function buildGrid(tbl) {
                const grid = [];
                Array.from(tbl.rows).forEach((row, r) => {
                    grid[r] = grid[r] || [];
                    let c = 0;
                    Array.from(row.cells).forEach((cell) => {
                        while (grid[r][c] !== undefined) c++;
                        const span = cell.colSpan || 1;
                        const className = cell.className || '';
                        const text = cell.textContent.trim();
                        for (let i = 0; i < span; i++) {
                            grid[r][c + i] = { value: i === 0 ? text : '', className };
                        }
                        c += span;
                    });
                });
                return grid;
            }

            const grid = buildGrid(table);
            const rowCount = grid.length;
            const colCount = grid.reduce((max, row) => Math.max(max, row.length), 0);

            const wb = new ExcelJS.Workbook();
            const ws = wb.addWorksheet('Report');
            const colWidths = new Array(colCount).fill(10);

            for (let r = 0; r < rowCount; r++) {
                for (let c = 0; c < colCount; c++) {
                    const info = grid[r] && grid[r][c];
                    const value = info ? info.value : '';
                    const className = info ? info.className : '';

                    // Kept as plain text throughout (never Number(value)):
                    // item codes like "0001" would lose their leading
                    // zero if converted to a number.
                    const xlsxCell = ws.getCell(r + 1, c + 1);
                    xlsxCell.value = value;
                    xlsxCell.alignment = { horizontal: 'center', vertical: 'middle' };
                    xlsxCell.border = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER };

                    const fillClass = className.split(/\s+/).find((cls) => FILL_MAP[cls]);
                    if (fillClass) {
                        xlsxCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + FILL_MAP[fillClass] } };
                        xlsxCell.font = { bold: true, color: { argb: WHITE_TEXT_CLASSES.has(fillClass) ? 'FFFFFFFF' : 'FF000000' } };
                    }

                    colWidths[c] = Math.max(colWidths[c], Math.min(value.length + 4, 50));
                }
            }

            colWidths.forEach((w, i) => { ws.getColumn(i + 1).width = w; });

            const buffer = await wb.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${filenamePrefix}_${today}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        }
    </script>
@endpush
