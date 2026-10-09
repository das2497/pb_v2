@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <h2 class="pageheader-title"> {{Auth::user()->name}} Dashboard </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a>
                                        </li>
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

                    @if (Auth::user()->role === 'o_admin' && Auth::user()->email === 'adminkusaldilshan@gmail.com')
                        <div class="row mb-2 g-2">
                            <div class="col-12 col-lg-3 d-grid">
                                <a href="/processing-transfer" id="refresh" class="btn btn-danger mb-2 w-100">Processing to
                                    Completed</a> <!-- Added w-100 -->
                            </div>
                        </div>
                    @endif

                    <div class="row mb-2 g-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-lg-3 d-grid">
                                            <button class="btn btn-success mb-2 w-100" onclick="loadDashboardData();">
                                                <span style="display: block;" id="rfs">
                                                    Refresh dashboard
                                                </span>
                                                <span style="display: none;" id="spn">
                                                    Loading...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (Auth::user()->role === 'o_admin')
                        <div class="row mb-2 g-2">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if (session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                        @if (session('error'))
                                            <div class="alert alert-danger">
                                                {{ session('error') }}
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-12 col-lg-3 d-grid">
                                                <a href="{{route('morning-summary')}}" id="refresh"
                                                    class="btn btn-primary mb-2 w-100">Refresh Morning Summary</a>
                                                <!-- Added w-100 -->
                                            </div>
                                            <div class="col-12 col-lg-3 d-grid">
                                                <a href="{{route('evening-summary')}}" id="refresh"
                                                    class="btn btn-primary mb-2 w-100">Refresh Evening Summary</a>
                                                <!-- Added w-100 -->
                                            </div>
                                            <div class="col-12 col-lg-3 d-grid">
                                                <a href="{{route('morning-shop-report')}}" id="refresh"
                                                    class="btn btn-primary mb-2 w-100">Refresh Morning Shop Report</a>
                                                <!-- Added w-100 -->
                                            </div>
                                            <div class="col-12 col-lg-3 d-grid">
                                                <a href="{{route('evening-shop-report')}}" id="refresh"
                                                    class="btn btn-primary mb-2 w-100">Refresh Evening Shop Report</a>
                                                <!-- Added w-100 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif


                    <div class="row">
                        <!-- ============================================================== -->
                        <!-- sales  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Pending Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1" id="pending_orders_count">
                                        </h1>
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
                                        <h1 class="mb-1" id="processing_orders_count">
                                        </h1>
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
                                        <h1 class="mb-1" id="complete_orders_count">
                                        </h1>
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
                                        <h1 class="mb-1" id="under_review_orders_count">
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end total orders  -->
                        <!-- ============================================================== -->
                    </div>

                    @if (Auth::user()->role != 'view')
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted">Today Total Revenue</h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1" id="today_total_revenue" </h1>
                                        </div>
                                        <div class="metric-label d-inline-block float-right font-weight-bold">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted">This week Total Revenue <small class="alert-warning p-1"
                                                id="weeks_gap">
                                            </small></h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1" id="last7Days_total_revenue">
                                            </h1>
                                        </div>
                                        <div class="metric-label d-inline-block float-right text-success font-weight-bold">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-muted">This Month Total Revenue <small class="alert-warning p-1"
                                                id="month_gap"> </small></h5>
                                        <div class="metric-value d-inline-block">
                                            <h1 class="mb-1" id="lastMonth_total_revenue">
                                            </h1>
                                        </div>
                                        <div class="metric-label d-inline-block float-right text-primary font-weight-bold">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- --------------------------------------------------------------------------------------------------------------                     -->

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Last 12 Months Revenue</h2>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Last 30 Days Revenue</h2>
                                <div class="card-body">
                                    <canvas id="past30DaysRevenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Top 10 Best Selling Shops</h2>
                                <div class="card-body">
                                    <canvas id="topSellingShopsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Top 10 Best Selling Items</h2>
                                <div class="card-body">
                                    <canvas id="topSellingItemsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <h2 class="card-header">Top Best Selling Reps All Time</h2>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <canvas id="topSellingRepsAllTimeChart"></canvas>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <table class="table">
                                                <thead class="table-info">
                                                    <tr>
                                                        <th>Rep Name</th>
                                                        <th>Total Sales</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="topRepsTableBody">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <h2 class="card-header">Top Best Selling Reps Today</h2>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <canvas id="topSellingRepsTodayChart"></canvas>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <table class="table">
                                                <thead class="table-info">
                                                    <tr>
                                                        <th>Rep Name</th>
                                                        <th>Total Sales</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="top_selling_reps_today">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <h2 class="card-header">Top Best Selling Reps Last 30 Days</h2>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <canvas id="topSellingRepsLast30DaysChart"></canvas>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <table class="table">
                                                <thead class="table-info">
                                                    <tr>
                                                        <th>Rep Name</th>
                                                        <th>Total Sales</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="top_selling_reps_last_30_days">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- -------------------------------------------------------------------------------------------------------------------                     -->

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
                                                    <th class="border-0">Outlet</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="latestOrdersBody">

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
                                                    <th class="border-0">Outlet</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="top10OrdersBody">

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
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        function initialLoad() {
            console.log('Welcome to the dashboard! Click OK to load the latest data.')
            axios.get('{{ route("order-admin.first-load-data") }}')
                .then(res => {
                    const d = res.data;

                    // ── Dashboard stats ──
                    document.getElementById('pending_orders_count').innerHTML = d.stats.pending_orders_count;
                    document.getElementById('processing_orders_count').innerHTML = d.stats.processing_orders_count;
                    document.getElementById('complete_orders_count').innerHTML = d.stats.complete_orders_count;
                    document.getElementById('under_review_orders_count').innerHTML = d.stats.under_review_orders_count;

                    document.getElementById('today_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.stats.today_total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2 });

                    document.getElementById('weeks_gap').innerHTML = `(${d.stats.startOfThisWeek} to ${d.stats.endOfThisWeek})`;
                    document.getElementById('last7Days_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.stats.last7Days_total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2 });

                    document.getElementById('month_gap').innerHTML = `(${d.stats.startOfThisMonth} to ${d.stats.endOfThisMonth})`;
                    document.getElementById('lastMonth_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.stats.lastMonth_total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2 });
                })
                .catch(err => {
                    console.error('Error fetching initial data:', err);
                });
        }

        function loadDashboardData() {
            document.getElementById('rfs').style.display = 'none';
            document.getElementById('spn').style.display = 'block';
            axios.get('{{ route("api.order-admin.dashboard-data") }}')
                .then(res => {
                    const d = res.data.stats; // shortcut for easier access

                    // Update Stats
                    document.getElementById('pending_orders_count').innerHTML = d.pending_orders_count;
                    document.getElementById('processing_orders_count').innerHTML = d.processing_orders_count;
                    document.getElementById('complete_orders_count').innerHTML = d.complete_orders_count;
                    document.getElementById('under_review_orders_count').innerHTML = d.under_review_orders_count;

                    document.getElementById('today_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.today_total_revenue).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    document.getElementById('weeks_gap').innerHTML = `(${d.startOfThisWeek} to ${d.endOfThisWeek})`;
                    document.getElementById('last7Days_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.last7Days_total_revenue).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    document.getElementById('month_gap').innerHTML = `(${d.startOfThisMonth} to ${d.endOfThisMonth})`;
                    document.getElementById('lastMonth_total_revenue').innerHTML =
                        'රු. ' + parseFloat(d.lastMonth_total_revenue).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    //--------------------------------------------------------------------------------------------------------------

                    // Fix: Correctly map months and amounts
                    const months = d.last12montsrevenue.map(item => {
                        const date = new Date(item.month + '-01');
                        return date.toLocaleDateString('en-US', {
                            month: 'short',
                            year: 'numeric'
                        });
                        // Example: "Jan 2025", "Feb 2025"
                    });

                    const amounts = d.last12montsrevenue.map(item => parseFloat(item.amount));

                    // Destroy previous chart if exists
                    if (window.myBarChart instanceof Chart) {
                        window.myBarChart.destroy();
                    }

                    const barCtx = document.getElementById('barChart').getContext('2d');

                    window.myBarChart = new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: months,
                            datasets: [{
                                label: 'Orders Amount (Rs)',
                                data: amounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        // Format Y-axis values with commas and currency symbol
                                        callback: function (value, index, values) {
                                            return 'Rs ' + value.toLocaleString(undefined, {
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    }
                                }
                            }
                        }
                    });

                    //--------------------------------------------------------------------------------------------------------------
                    // === TOP 10 SELLING SHOPS PIE CHART ===
                    const ctx = document.getElementById('topSellingShopsChart').getContext('2d');
                    let chart = null;

                    const {
                        topSellingShops_labels,
                        topSellingShops_data
                    } = d.topSellingShops;

                    chart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: topSellingShops_labels,
                            datasets: [{
                                label: 'Total Sales',
                                data: topSellingShops_data,
                                backgroundColor: [
                                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                                    '#FF9F40', '#E7E9ED', '#8C9EFF', '#00CC99', '#FF99CC'
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Selling Shops'
                                },
                            }
                        }
                    });
                    //-------------------------------------------------------------------------------------------------------------
                    const ctxItems = document.getElementById('topSellingItemsChart').getContext('2d');
                    let chartItems = null;

                    // Safe destructuring with defaults
                    const {
                        topItems_labels,
                        topItems_data
                    } = d.topSellingItems || {};

                    // Destroy existing chart
                    if (chartItems) {
                        chartItems.destroy();
                    }

                    chartItems = new Chart(ctxItems, {
                        type: 'pie',
                        data: {
                            labels: topItems_labels,
                            datasets: [{
                                label: 'Total Quantity Sold',
                                data: topItems_data,
                                backgroundColor: [
                                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                                    '#FF9F40', '#E7E9ED', '#8C9EFF', '#00CC99', '#FF99CC'
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Selling Items'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            return `${label}: ${value} units`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    //--------------------------------------------------------------------------------------------------------------
                    // last 30 days revenue chart update
                    const ctx30days = document.getElementById('past30DaysRevenueChart').getContext('2d');
                    let chart30days = null;
                    const {
                        labels: revenueLabels,
                        data: revenueData
                    } = d.revenueData;
                    // Destroy previous chart if exists
                    if (chart30days instanceof Chart) {
                        chart30days.destroy();
                    }
                    chart30days = new Chart(ctx30days, {
                        type: 'bar',
                        data: {
                            labels: revenueLabels,
                            datasets: [{
                                label: 'Revenue (Rs)',
                                data: revenueData,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Past 30 Days Revenue'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Revenue (Rs)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                }
                            }
                        }
                    });

                    //--------------------------------------------------------------------------------------------------------------
                    // Update Top Selling Reps all Time Chart
                    const ctxRepsAllTime = document.getElementById('topSellingRepsAllTimeChart').getContext('2d');
                    let chartRepsAllTime = null;
                    const {
                        labels: repLabelsAllTime,
                        data: repDataAllTime
                    } = d.topSellingRepsAllTime.chartData;
                    // Destroy previous chart if exists
                    if (chartRepsAllTime instanceof Chart) {
                        chartRepsAllTime.destroy();
                    }
                    chartRepsAllTime = new Chart(ctxRepsAllTime, {
                        type: 'bar',
                        data: {
                            labels: repLabelsAllTime,
                            datasets: [{
                                label: 'Total Sales (Rs)',
                                data: repDataAllTime,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Best-Selling Reps'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Sales (Rs)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Rep Name'
                                    }
                                }
                            }
                        }
                    });

                    const tableBody = document.getElementById('topRepsTableBody');
                    // Clear existing table rows
                    tableBody.innerHTML = '';
                    // Populate table with new data
                    d.topSellingRepsAllTime.tableData.forEach(rep => {
                        const row = document.createElement('tr');
                        const nameCell = document.createElement('td');
                        nameCell.textContent = rep.name;
                        const salesCell = document.createElement('td');
                        salesCell.textContent = 'Rs.' + parseFloat(rep.total_sales).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        row.appendChild(nameCell);
                        row.appendChild(salesCell);
                        tableBody.appendChild(row);
                    });
                    //-------------------------------------------------------------------------------------------------
                    // Update Top Selling Reps Today Chart
                    const ctxRepsToday = document.getElementById('topSellingRepsTodayChart').getContext('2d');
                    let chartRepsToday = null;
                    const {
                        labels: repLabelsToday,
                        data: repDataToday
                    } = d.topSellingRepsToday.chartData;
                    // Destroy previous chart if exists
                    if (chartRepsToday instanceof Chart) {
                        chartRepsToday.destroy();
                    }
                    chartRepsToday = new Chart(ctxRepsToday, {
                        type: 'bar',
                        data: {
                            labels: repLabelsToday,
                            datasets: [{
                                label: 'Total Sales (Rs)',
                                data: repDataToday,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Best-Selling Reps'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Sales (Rs)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Rep Name'
                                    }
                                }
                            }
                        }
                    });

                    // Update Top Selling Reps Today Table
                    const tableBodyToday = document.getElementById('top_selling_reps_today');
                    // Clear existing table rows
                    tableBodyToday.innerHTML = '';
                    // Populate table with new data
                    d.topSellingRepsToday.tableData.forEach(rep => {
                        const row = document.createElement('tr');
                        const nameCell = document.createElement('td');
                        nameCell.textContent = rep.name;
                        const salesCell = document.createElement('td');
                        salesCell.textContent = 'Rs.' + parseFloat(rep.total_sales).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        row.appendChild(nameCell);
                        row.appendChild(salesCell);
                        tableBodyToday.appendChild(row);
                    });

                    //-------------------------------------------------------------------------------------------------
                    // Update Top Selling Reps Last 30 Days Chart
                    const ctxReps30Days = document.getElementById('topSellingRepsLast30DaysChart').getContext('2d');
                    let chartReps30Days = null;
                    const {
                        labels: repLabels30Days,
                        data: repData30Days
                    } = d.topSellingRepsLast30Days.chartData;
                    // Destroy previous chart if exists
                    if (chartReps30Days instanceof Chart) {
                        chartReps30Days.destroy();
                    }
                    chartReps30Days = new Chart(ctxReps30Days, {
                        type: 'bar',
                        data: {
                            labels: repLabels30Days,
                            datasets: [{
                                label: 'Total Sales (Rs)',
                                data: repData30Days,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Best-Selling Reps'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Sales (Rs)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Rep Name'
                                    }
                                }
                            }
                        }
                    });

                    // Update Top Selling Reps Last 30 Days Table
                    const tableBody30Days = document.getElementById('top_selling_reps_last_30_days');
                    // Clear existing table rows
                    tableBody30Days.innerHTML = '';
                    // Populate table with new data
                    d.topSellingRepsLast30Days.tableData.forEach(rep => {
                        const row = document.createElement('tr');
                        const nameCell = document.createElement('td');
                        nameCell.textContent = rep.name;
                        const salesCell = document.createElement('td');
                        salesCell.textContent = 'Rs.' + parseFloat(rep.total_sales).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        row.appendChild(nameCell);
                        row.appendChild(salesCell);
                        tableBody30Days.appendChild(row);
                    });

                    //-------------------------------------------------------------------------------------------------
                    // Update Latest Orders Table
                    const latestOrdersBody = document.getElementById('latestOrdersBody');
                    // Clear existing table rows
                    latestOrdersBody.innerHTML = '';
                    // Populate table with new data
                    d.latest_orders.forEach((order, index) => {
                        const row = document.createElement('tr');
                        const indexCell = document.createElement('td');
                        indexCell.textContent = index + 1;
                        const orderIdCell = document.createElement('td');
                        orderIdCell.textContent = order.id;
                        const outletCell = document.createElement('td');
                        outletCell.textContent = order.shop_name || order.shop;
                        const deliveryTimeCell = document.createElement('td');
                        deliveryTimeCell.textContent = order.time_period || 'N/A';
                        const orderCreateTimeCell = document.createElement('td');
                        const createdAt = new Date(order.order_created_at);
                        orderCreateTimeCell.textContent = createdAt.toLocaleString();
                        const estimateAmountCell = document.createElement('td');
                        estimateAmountCell.textContent = 'Rs. ' + parseFloat(order.total_price).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        row.appendChild(indexCell);
                        row.appendChild(orderIdCell);
                        row.appendChild(outletCell);
                        row.appendChild(deliveryTimeCell);
                        row.appendChild(orderCreateTimeCell);
                        row.appendChild(estimateAmountCell);
                        latestOrdersBody.appendChild(row);
                    });

                    //-------------------------------------------------------------------------------------------------
                    // Update Top 10 Orders Table
                    const top10OrdersBody = document.getElementById('top10OrdersBody');
                    // Clear existing table rows
                    top10OrdersBody.innerHTML = '';
                    // Populate table with new data
                    d.top_orders.forEach((order, index) => {
                        const row = document.createElement('tr');
                        const indexCell = document.createElement('td');
                        indexCell.textContent = index + 1;
                        const orderIdCell = document.createElement('td');
                        orderIdCell.textContent = order.unique_id;
                        const outletCell = document.createElement('td');
                        outletCell.textContent = order.name || order.shop;
                        const deliveryTimeCell = document.createElement('td');
                        deliveryTimeCell.textContent = order.time_period || 'N/A';
                        const orderCreateTimeCell = document.createElement('td');
                        const createdAt = new Date(order.order_created_at);
                        orderCreateTimeCell.textContent = createdAt.toLocaleString();
                        const estimateAmountCell = document.createElement('td');
                        estimateAmountCell.textContent = 'Rs. ' + parseFloat(order.total_price).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        row.appendChild(indexCell);
                        row.appendChild(orderIdCell);
                        row.appendChild(outletCell);
                        row.appendChild(deliveryTimeCell);
                        row.appendChild(orderCreateTimeCell);
                        row.appendChild(estimateAmountCell);
                        top10OrdersBody.appendChild(row);
                    });

                })
                .catch(err => {
                    console.error('Error loading dashboard data:', err);
                    alert('Failed to load dashboard data. Please try again.');
                })
                .finally(() => {
                    document.getElementById('rfs').style.display = 'block';
                    document.getElementById('spn').style.display = 'none';
                });
        }

        let intervalId;

        // document.addEventListener('DOMContentLoaded', function () {
        //     // Initial load
        //     loadDashboardData();

        //     // Refresh every 5 minutes (300000 milliseconds)
        //     intervalId = setInterval(loadDashboardData, 20000);
        // });

        // document.addEventListener('beforeunload', function () {
        //     clearInterval(intervalId);
        // });

        document.getElementById('refresh').addEventListener('click', function () {
            // Disable the button
            // loadDashboardData();

            this.disabled = true;
        });

        initialLoad();
    </script>
@endpush
