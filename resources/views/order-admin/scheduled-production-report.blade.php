@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Scheduled Production Report')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Scheduled Production Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Scheduled Production Report</li>
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
                                    <div class="col-12 col-lg-4">
                                        <h5 class="card-header">
                                            Products in Scheduled Orders &nbsp;&nbsp;
                                            <button type="button" id="exportBtn" class="btn btn-success" onclick="exportReport()">
                                                Export to Excel
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="col-12 col-lg-8 pt-3">
                                        <form class="row g-2" action="/order-admin/scheduled-production-report" method="GET">
                                            @csrf
                                            <div class="col-6 col-sm-4">
                                                <label class="form-label mb-1">From</label>
                                                <input type="date" name="from" class="form-control" value="{{ $from }}">
                                            </div>
                                            <div class="col-6 col-sm-4">
                                                <label class="form-label mb-1">To</label>
                                                <input type="date" name="to" class="form-control" value="{{ $to }}">
                                            </div>
                                            <div class="col-12 col-sm-4 d-flex align-items-end">
                                                <button class="btn btn-primary w-100" type="submit">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="row p-3">
                                    <div class="col-6 col-lg-3">
                                        <div class="card bg-light p-3 mb-0">
                                            <small class="text-muted">Date range</small>
                                            <strong>{{ \Carbon\Carbon::parse($from)->format('M d') }} - {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="card bg-light p-3 mb-0">
                                            <small class="text-muted">Scheduled orders</small>
                                            <strong>{{ $orderCount }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="card bg-light p-3 mb-0">
                                            <small class="text-muted">Shops</small>
                                            <strong>{{ $shopCount }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="card bg-light p-3 mb-0">
                                            <small class="text-muted">Distinct products</small>
                                            <strong>{{ $products->count() }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body p-0">
                                    <div style="max-height: 70vh; overflow: auto;">
                                        <table class="table table-bordered border-dark mb-0" id="exportTable">
                                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 2;">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Item Code</th>
                                                    <th>Product</th>
                                                    <th>Category</th>
                                                    <th>Total Quantity</th>
                                                    <th>Shops</th>
                                                    <th>Scheduled Orders</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($products as $product)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $product->item_number }}</td>
                                                    <td>{{ $product->name_english }}</td>
                                                    <td>{{ $product->category }}</td>
                                                    <td><strong>{{ rtrim(rtrim(number_format($product->total_qty, 2), '0'), '.') }}</strong></td>
                                                    <td>{{ $product->shop_count }}</td>
                                                    <td>{{ $product->order_count }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">No scheduled orders for this date range.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
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
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function exportReport() {
        const btn = document.getElementById('exportBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Exporting...';

        try {
            const table = document.getElementById('exportTable');
            const wb = XLSX.utils.table_to_book(table, { sheet: 'Production Report' });
            const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            XLSX.writeFile(wb, `Scheduled_Production_Report_${today}.xlsx`);
        } catch (e) {
            alert('Export failed: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
@endpush
