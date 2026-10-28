@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Rep | Dashboard')

@push('styles')
<style>
    .rep-dash-skeleton {
        display: inline-block;
        min-width: 70px;
        min-height: 1em;
        border-radius: 6px;
        color: transparent !important;
        background: linear-gradient(90deg, #eee 25%, #ddd 37%, #eee 63%);
        background-size: 400% 100%;
        animation: rep-dash-skeleton-shimmer 1.4s ease infinite;
    }

    @keyframes rep-dash-skeleton-shimmer {
        0% { background-position: 100% 50%; }
        100% { background-position: 0 50%; }
    }

    .rep-dash-chart-empty-msg {
        text-align: center;
        color: #999;
        padding: 40px 10px;
        margin: 0;
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
                            <h2 class="pageheader-title"> Rep Dashboard </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
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
                        <!-- sales  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Pending Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="pending_orders_count">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end sales  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- new customer  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Processing Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="processing_orders_count">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end new customer  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- visitor  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Completed Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="complete_orders_count">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end visitor  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- total orders  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Under Review Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="under_review_orders_count">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end total orders  -->
                        <!-- ============================================================== -->
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">Today estimated Total Revenue</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="today_total_revenue">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">This week estimated Total Revenue <small class="alert-warning p-1" id="weeks_gap"></small></h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="thisWeek_total_revenue">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">This Month estimated Total Revenue <small class="alert-warning p-1" id="month_gap"></small></h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 rep-dash-skeleton" id="lastMonth_total_revenue">&nbsp;</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row d-none">
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-6 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Last 7 days morning estimated value</h5>
                                <div class="card-body">
                                    <canvas id="chartjs_bar_m"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Last 7 days evining estimated value</h5>
                                <div class="card-body">
                                    <canvas id="chartjs_bar_e"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Last 30 days Revenues</h2>
                                <div class="card-body">
                                    <canvas id="barChart2"></canvas>
                                    <p class="rep-dash-chart-empty-msg d-none">No revenue yet in the last 30 days.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Last 12 Months Revenues</h2>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                    <p class="rep-dash-chart-empty-msg d-none">No revenue yet for the last 12 months.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Shops Revenues</h2>
                                <div class="card-body">
                                <canvas id="revenuePieChart"></canvas>
                                <p class="rep-dash-chart-empty-msg d-none">No revenue yet to show a breakdown.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Resent Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Shop</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="latestOrdersBody">
                                                <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Top 10 Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Shop</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="topOrdersBody">
                                                <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
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
        <!-- ============================================================== -->
        <!-- end footer -->
        <!-- ============================================================== -->
    </div>

    
@endsection

@push('scripts')
<script>
    const shopRevenuePalette = [
       'rgba(255, 99, 132, 0.6)',    'rgba(54, 162, 235, 0.6)',    'rgba(255, 206, 86, 0.6)',     'rgba(75, 192, 192, 0.6)',    'rgba(153, 102, 255, 0.6)',
       'rgba(255, 159, 64, 0.6)',    'rgba(255, 99, 71, 0.6)',     'rgba(0, 128, 128, 0.6)',      'rgba(128, 0, 128, 0.6)',      'rgba(0, 255, 127, 0.6)',
       'rgba(70, 130, 180, 0.6)',    'rgba(210, 105, 30, 0.6)',    'rgba(255, 140, 0, 0.6)',      'rgba(0, 139, 139, 0.6)',      'rgba(139, 0, 139, 0.6)',
       'rgba(0, 255, 255, 0.6)',     'rgba(255, 0, 255, 0.6)',     'rgba(255, 215, 0, 0.6)',      'rgba(218, 112, 214, 0.6)',    'rgba(50, 205, 50, 0.6)',
       'rgba(72, 209, 204, 0.6)',    'rgba(199, 21, 133, 0.6)',    'rgba(25, 25, 112, 0.6)',      'rgba(245, 222, 179, 0.6)',    'rgba(255, 228, 196, 0.6)',
       'rgba(139, 69, 19, 0.6)',     'rgba(0, 128, 0, 0.6)',       'rgba(255, 105, 180, 0.6)',    'rgba(128, 128, 0, 0.6)',      'rgba(173, 216, 230, 0.6)',
       'rgba(240, 128, 128, 0.6)',   'rgba(224, 255, 255, 0.6)',   'rgba(250, 235, 215, 0.6)',    'rgba(127, 255, 212, 0.6)',    'rgba(255, 182, 193, 0.6)',
       'rgba(255, 250, 205, 0.6)',   'rgba(240, 248, 255, 0.6)',   'rgba(220, 20, 60, 0.6)',      'rgba(0, 255, 127, 0.6)',      'rgba(255, 69, 0, 0.6)',
       'rgba(0, 191, 255, 0.6)',     'rgba(255, 20, 147, 0.6)',    'rgba(123, 104, 238, 0.6)',    'rgba(106, 90, 205, 0.6)',     'rgba(112, 128, 144, 0.6)',
       'rgba(176, 196, 222, 0.6)',   'rgba(255, 127, 80, 0.6)',    'rgba(147, 112, 219, 0.6)',    'rgba(60, 179, 113, 0.6)',     'rgba(46, 139, 87, 0.6)',
       'rgba(102, 205, 170, 0.6)',   'rgba(143, 188, 143, 0.6)',   'rgba(32, 178, 170, 0.6)',     'rgba(0, 206, 209, 0.6)',      'rgba(95, 158, 160, 0.6)',
       'rgba(72, 61, 139, 0.6)',     'rgba(139, 0, 0, 0.6)',       'rgba(233, 150, 122, 0.6)',    'rgba(255, 160, 122, 0.6)',    'rgba(255, 192, 203, 0.6)',
       'rgba(221, 160, 221, 0.6)',   'rgba(176, 224, 230, 0.6)',   'rgba(152, 251, 152, 0.6)',    'rgba(175, 238, 238, 0.6)',    'rgba(219, 112, 147, 0.6)',
       'rgba(238, 232, 170, 0.6)',   'rgba(250, 250, 210, 0.6)',
    ];

    function money(value) {
        return 'රු. ' + parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function clearSkeleton(id, html) {
        const el = document.getElementById(id);
        el.classList.remove('rep-dash-skeleton');
        el.innerHTML = html;
    }

    function renderOrdersTable(bodyId, orders) {
        const body = document.getElementById(bodyId);
        if (!orders || orders.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>';
            return;
        }
        body.innerHTML = orders.map((order, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${order.unique_id}</td>
                <td>${order.shop_name}</td>
                <td>${order.time_period}</td>
                <td>${order.order_date}</td>
                <td>රු. ${parseFloat(order.total_price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            </tr>
        `).join('');
    }

    // Chart.js still draws axes/legend for an all-zero dataset, which reads
    // as "something's broken" rather than "no sales yet" - show a plain
    // message instead for a rep with no history.
    function renderChartOrEmptyState(canvasId, values, renderFn) {
        const canvas = document.getElementById(canvasId);
        const hasData = values.some(v => parseFloat(v) > 0);
        if (!hasData) {
            canvas.classList.add('d-none');
            canvas.closest('.card-body').querySelector('.rep-dash-chart-empty-msg').classList.remove('d-none');
            return;
        }
        renderFn();
    }

    // ── Fast path: order status counts + revenue, paints almost instantly ──
    fetch('{{ route('rep.first-load-data') }}')
        .then(res => res.json())
        .then(res => {
            const d = res.stats;
            clearSkeleton('pending_orders_count', d.pending_orders_count);
            clearSkeleton('processing_orders_count', d.processing_orders_count);
            clearSkeleton('complete_orders_count', d.complete_orders_count);
            clearSkeleton('under_review_orders_count', d.under_review_orders_count);
            clearSkeleton('today_total_revenue', money(d.today_total_revenue));
            clearSkeleton('thisWeek_total_revenue', money(d.thisWeek_total_revenue));
            clearSkeleton('lastMonth_total_revenue', money(d.lastMonth_total_revenue));
            document.getElementById('weeks_gap').textContent = `(${d.startOfThisWeek} to ${d.endOfThisWeek})`;
            document.getElementById('month_gap').textContent = `(${d.startOfThisMonth} to ${d.endOfThisMonth})`;
        })
        .catch(err => console.error('Error fetching dashboard stats:', err));

    // ── Slower path: charts + leaderboards, loads in parallel with the above ──
    fetch('{{ route('api.rep.dashboard-data') }}')
        .then(res => res.json())
        .then(res => {
            const d = res.stats;

            renderOrdersTable('latestOrdersBody', d.latest_orders);
            renderOrdersTable('topOrdersBody', d.top_orders);

            const months = d.last12monthsRevenue.map(item => item.month);
            const amounts = d.last12monthsRevenue.map(item => item.amount);

            renderChartOrEmptyState('barChart', amounts, () => new Chart(document.getElementById('barChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Revenue (Rs)',
                        data: amounts,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            }));

            const dates = d.last30DaysRevenue.map(item => item.date);
            const amounts30 = d.last30DaysRevenue.map(item => item.amount);

            renderChartOrEmptyState('barChart2', amounts30, () => new Chart(document.getElementById('barChart2').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Revenue (Rs)',
                        data: amounts30,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            }));

            const shopRevenues = d.shopRevenues.map(s => s.revenue);

            renderChartOrEmptyState('revenuePieChart', shopRevenues, () => new Chart(document.getElementById('revenuePieChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: d.shopRevenues.map(s => s.shop),
                    datasets: [{
                        label: 'Revenue',
                        data: shopRevenues,
                        backgroundColor: shopRevenuePalette,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Shop Revenues' },
                    }
                }
            }));
        })
        .catch(err => console.error('Error fetching dashboard charts:', err));
    </script>
@endpush
