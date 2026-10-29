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
                            <h2 class="pageheader-title"> Morning Shop Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Morning Shop Report</li>
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
                            <a class="btn btn-outline-primary mb-4" href="/order-admin/shop-report-evening">Evining Report</a>
                        </div>
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-12">
                            <div class="card">
                                <div class="row">
                                    <div class="col-12 col-lg-4">
                                        <h5 class="card-header">Today Morning Shop Report &nbsp;&nbsp;
                                            <a href="/order-admin/shop-report-full-screen" type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#morning_shop_wise_report">
                                                Full Screen
                                            </a>
                                            <button type="button" id="exportBtn" class="btn btn-success" onclick="exportFullReport()">
                                                Export to Excel
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="col-12 col-lg-8 pt-3">
                                        <form class="row g-2" action="/order-admin/shop-report" method="GET">
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
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 280px;">Item Code | Item</th>

                                                    {{-- Normal --}}
                                                    @foreach ($header_normal as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{ $head->shop_name }}</h6>
                                                        <h6>{{ $head->branch_code }}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-secondary">
                                                        <h6>{{ $route->first()->route_name }} Total</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-primary">
                                                        <h6>Normal Route Total</h6>
                                                    </th>

                                                    {{-- Special --}}
                                                    @foreach ($header_special as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{ $head->shop_name }}</h6>
                                                        <h6>{{ $head->branch_code }}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-secondary">
                                                        <h6>{{ $route->first()->route_name }} Total</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-danger">
                                                        <h6>Special Route Total</h6>
                                                    </th>

                                                    {{-- PBD --}}
                                                    @foreach ($header_pbd as $route)
                                                    @foreach ($route as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{ $head->shop_name }}</h6>
                                                        <h6>{{ $head->branch_code }}</h6>
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

                                            <tbody>
                                                @foreach ($products as $product)
                                                @php
                                                $total_normal = $total_special = $total_pbd = 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                                                    {{-- Normal --}}
                                                    @foreach ($header_normal as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $qty = $order['qty'] ?? 0;
                                                    $remark = $order['remark'] ?? '';
                                                    $route_total += $qty;
                                                    $total_normal += $qty;
                                                    @endphp
                                                    <td class="{{ $qty ? 'table-success' : '' }}">{{ $qty }}</td>
                                                    <td style="max-width: 180px; overflow-x: auto;"><span>{{ $remark }}</span></td>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach
                                                    <td class="table-primary">{{ $total_normal }}</td>

                                                    {{-- Special --}}
                                                    @foreach ($header_special as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $qty = $order['qty'] ?? 0;
                                                    $remark = $order['remark'] ?? '';
                                                    $route_total += $qty;
                                                    $total_special += $qty;
                                                    @endphp
                                                    <td class="{{ $qty ? 'table-success' : '' }}">{{ $qty }}</td>
                                                    <td style="max-width: 180px; overflow-x: auto;"><span>{{ $remark }}</span></td>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach
                                                    <td class="table-danger">{{ $total_special }}</td>

                                                    {{-- PBD --}}
                                                    @foreach ($header_pbd as $route)
                                                    @php $route_total = 0; @endphp
                                                    @foreach ($route as $head)
                                                    @php
                                                    $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                                    $qty = $order['qty'] ?? 0;
                                                    $remark = $order['remark'] ?? '';
                                                    $route_total += $qty;
                                                    $total_pbd += $qty;
                                                    @endphp
                                                    <td class="{{ $qty ? 'table-success' : '' }}">{{ $qty }}</td>
                                                    <td style="max-width: 180px; overflow-x: auto;"><span>{{ $remark }}</span></td>
                                                    @endforeach
                                                    <td class="table-secondary">{{ $route_total }}</td>
                                                    @endforeach
                                                    <td class="table-danger">{{ $total_pbd }}</td>

                                                    {{-- Grand Total --}}
                                                    <td class="table-warning">{{ $total_normal + $total_special + $total_pbd }}</td>
                                                </tr>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                const res = await fetch('/order-admin/shop-report/export?' + params.toString());
                if (!res.ok) throw new Error('Export request failed');

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const table = doc.getElementById('exportTable');
                if (!table) throw new Error('Export table not found');

                downloadTableAsExcel(table, 'Morning_Shop_Report');
            } catch (e) {
                alert('Export failed: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function downloadTableAsExcel(table, filenamePrefix) {
            const wb = XLSX.utils.table_to_book(table, { sheet: 'Report' });
            const ws = wb.Sheets['Report'];
            const range = XLSX.utils.decode_range(ws['!ref']);

            const borderStyle = {
                top: { style: 'thin' },
                bottom: { style: 'thin' },
                left: { style: 'thin' },
                right: { style: 'thin' }
            };

            for (let R = range.s.r; R <= range.e.r; ++R) {
                for (let C = range.s.c; C <= range.e.c; ++C) {
                    const addr = XLSX.utils.encode_cell({ r: R, c: C });
                    if (!ws[addr]) ws[addr] = { v: '' };
                    ws[addr].s = ws[addr].s || {};
                    ws[addr].s.border = borderStyle;
                    ws[addr].s.alignment = { horizontal: 'center', vertical: 'center' };
                }
            }

            const colWidths = [];
            for (let C = range.s.c; C <= range.e.c; ++C) {
                let maxWidth = 10;
                for (let R = range.s.r; R <= range.e.r; ++R) {
                    const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
                    if (cell && cell.v !== undefined && cell.v.toString().length > maxWidth) {
                        maxWidth = cell.v.toString().length;
                    }
                }
                colWidths.push({ wch: Math.min(maxWidth + 4, 50) });
            }
            ws['!cols'] = colWidths;

            const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            XLSX.writeFile(wb, `${filenamePrefix}_${today}.xlsx`);
        }
    </script>
@endpush
