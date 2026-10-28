@extends('layouts.shop-dashboard')

@section('title', 'Shop')

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
                            <h2 class="pageheader-title"> Dashboard </h2>
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

                    <div class="bakery-welcome-banner">
                        <div>
                            <h2>Welcome back, {{ Auth::user()->name }} 🥐</h2>
                            <p>Here's how your bakery orders are doing today.</p>
                        </div>
                        <a href="/shop/create-order" class="btn btn-light btn-sm"><i class="fa fa-cart-plus"></i> Create Order</a>
                    </div>

                    <div class="row">
                        <!-- ============================================================== -->
                        <!-- sales  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary bakery-stat-card">
                                <div class="card-body">
                                    <div class="bakery-stat-icon is-pending"><i class="fa fa-clock"></i></div>
                                    <div>
                                        <h5 class="text-muted">Pending Orders</h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1 bakery-skeleton" id="pending_orders_count">&nbsp;</h1>
                                        </div>
                                        <div class="bakery-stat-caption">Waiting to be accepted</div>
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
                            <div class="card border-3 border-top border-top-primary bakery-stat-card">
                                <div class="card-body">
                                    <div class="bakery-stat-icon is-processing"><i class="fa fa-fire"></i></div>
                                    <div>
                                        <h5 class="text-muted">Processing Orders</h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1 bakery-skeleton" id="processing_orders_count">&nbsp;</h1>
                                        </div>
                                        <div class="bakery-stat-caption">Being prepared right now</div>
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
                            <div class="card border-3 border-top border-top-primary bakery-stat-card">
                                <div class="card-body">
                                    <div class="bakery-stat-icon is-complete"><i class="fa fa-check-circle"></i></div>
                                    <div>
                                        <h5 class="text-muted">Completed Orders</h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1 bakery-skeleton" id="complete_orders_count">&nbsp;</h1>
                                        </div>
                                        <div class="bakery-stat-caption">Delivered successfully</div>
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
                            <div class="card border-3 border-top border-top-primary bakery-stat-card">
                                <div class="card-body">
                                    <div class="bakery-stat-icon is-review"><i class="fa fa-search"></i></div>
                                    <div>
                                        <h5 class="text-muted">Under Review</h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1 bakery-skeleton" id="under_review_orders_count">&nbsp;</h1>
                                        </div>
                                        <div class="bakery-stat-caption">Needs your attention</div>
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
                                    <h5 class="text-muted"><i class="fa fa-calendar"></i> Today's Revenue</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 bakery-skeleton" id="today_total_revenue">&nbsp;</h1>
                                    </div>
                                    <div class="bakery-stat-caption">Estimated, from processing &amp; completed orders</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted"><i class="fa fa-calendar-alt"></i> This Week's Revenue</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 bakery-skeleton" id="thisWeek_total_revenue">&nbsp;</h1>
                                    </div>
                                    <div class="bakery-stat-caption" id="weeks_gap"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted"><i class="fa fa-calendar-alt"></i> This Month's Revenue</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1 bakery-skeleton" id="lastMonth_total_revenue">&nbsp;</h1>
                                    </div>
                                    <div class="bakery-stat-caption" id="month_gap"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-chart-bar"></i> Last 12 Months Revenue</h5>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                    <p class="bakery-chart-empty-msg d-none">No revenue yet for the last 12 months.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-chart-bar"></i> Last 30 Days Revenue</h5>
                                <div class="card-body">
                                    <canvas id="past30DaysRevenueChart"></canvas>
                                    <p class="bakery-chart-empty-msg d-none">No revenue yet in the last 30 days.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-chart-pie"></i> My Best Selling Product Categories</h5>
                                <div class="card-body">
                                    <canvas id="bestSellingProductCategories"></canvas>
                                    <p class="bakery-chart-empty-msg d-none">No sales yet to show a breakdown.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-trophy"></i> My Best Selling Products</h5>
                                <div class="card-body">
                                    <canvas id="bestSellingProducts"></canvas>
                                    <p class="bakery-chart-empty-msg d-none">No sales yet to show a breakdown.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-list-ul"></i> Recent Orders <a href="/shop/all-orders" class="float-right">View All</a></h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order #</th>
                                                    <th class="border-0">Delivery Time</th>
                                                    <th class="border-0">Ordered On</th>
                                                    <th class="border-0">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="latestOrdersBody">
                                                <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header"><i class="fa fa-trophy"></i> Last 30 Days Top 10 Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order #</th>
                                                    <th class="border-0">Delivery Time</th>
                                                    <th class="border-0">Ordered On</th>
                                                    <th class="border-0">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="topOrdersBody">
                                                <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
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
    function money(value) {
        return 'රු. ' + parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function clearSkeleton(id, html) {
        const el = document.getElementById(id);
        el.classList.remove('bakery-skeleton');
        el.innerHTML = html;
    }

    function formatOrderDate(value) {
        const date = new Date(String(value).replace(' ', 'T'));
        if (isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' });
    }

    function renderOrdersTable(bodyId, orders) {
        const body = document.getElementById(bodyId);
        if (!orders || orders.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">🧁 No orders yet - they\'ll show up here once you place one.</td></tr>';
            return;
        }
        body.innerHTML = orders.map((order, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${order.unique_id}</td>
                <td>${order.time_period}</td>
                <td>${formatOrderDate(order.order_date)}</td>
                <td>රු. ${parseFloat(order.total_price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            </tr>
        `).join('');
    }

    // Chart.js still draws axes/legend for an all-zero dataset, which reads
    // as "something's broken" rather than "no sales yet" - show a plain
    // message instead for a brand new shop with no history.
    function renderChartOrEmptyState(canvasId, values, renderFn) {
        const canvas = document.getElementById(canvasId);
        const hasData = values.some(v => parseFloat(v) > 0);
        if (!hasData) {
            canvas.classList.add('d-none');
            canvas.closest('.card-body').querySelector('.bakery-chart-empty-msg').classList.remove('d-none');
            return;
        }
        renderFn();
    }

    // ── Fast path: order status counts + revenue, paints almost instantly ──
    fetch('{{ route('shop.first-load-data') }}')
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
    fetch('{{ route('api.shop.dashboard-data') }}')
        .then(res => res.json())
        .then(res => {
            const d = res.stats;

            renderOrdersTable('latestOrdersBody', d.latest_orders);
            renderOrdersTable('topOrdersBody', d.top_orders);

            const months = d.last12montsrevenue.map(item => item.month);
            const amounts = d.last12montsrevenue.map(item => item.amount);

            renderChartOrEmptyState('barChart', amounts, () => new Chart(document.getElementById('barChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Orders Amount (Rs)',
                        data: amounts,
                        backgroundColor: 'rgba(224, 123, 26, 0.25)',
                        borderColor: 'rgba(224, 123, 26, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            }));

            const dates = d.last30DaysRevenue.map(item => item.date);
            const amounts30 = d.last30DaysRevenue.map(item => item.amount);

            renderChartOrEmptyState('past30DaysRevenueChart', amounts30, () => new Chart(document.getElementById('past30DaysRevenueChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Revenue (Rs)',
                        data: amounts30,
                        backgroundColor: 'rgba(107, 66, 38, 0.2)',
                        borderColor: 'rgba(107, 66, 38, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            }));

            const bakeryPalette = [
                '#e07b1a', '#6b4226', '#d4a017', '#6a994e', '#c1443c',
                '#8a5a37', '#f2c14e', '#a1876b', '#4a2c17', '#e3a857'
            ];

            const categoryQtys = d.bestSellingCategories.map(c => c.total_quantity_sold);
            renderChartOrEmptyState('bestSellingProductCategories', categoryQtys, () => new Chart(document.getElementById('bestSellingProductCategories').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: d.bestSellingCategories.map(c => c.category),
                    datasets: [{
                        label: 'Total Sales',
                        data: categoryQtys,
                        backgroundColor: bakeryPalette,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Best Selling Products Categories' },
                    }
                }
            }));

            const productQtys = d.topSellingProducts.map(p => p.total_quantity_sold);
            renderChartOrEmptyState('bestSellingProducts', productQtys, () => new Chart(document.getElementById('bestSellingProducts').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: d.topSellingProducts.map(p => p.name_sinhala),
                    datasets: [{
                        label: 'Total Sales',
                        data: productQtys,
                        backgroundColor: bakeryPalette,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Best Selling Products' },
                    }
                }
            }));
        })
        .catch(err => console.error('Error fetching dashboard charts:', err));
</script>
@endpush
