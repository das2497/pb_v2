<div class="nav-left-sidebar sidebar-dark">
    <div class="menu-list">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="d-xl-none d-lg-none" href="/order-admin/dashboard">Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-divider">
                        Menu
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link active" href="/order-admin/dashboard"><i class="fa fa-fw fa-user-circle"></i>Dashboard <span class="badge badge-success">6</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-sticky-note"></i>Orders</a>
                        <div id="submenu-2" class="collapse submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/order-admin/pending-orders">Pending Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/order-admin/processing-orders">Procesing Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/order-admin/under-review-orders">Under Review Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/order-admin/complete-orders">Completed Orders</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @if (Auth::user()->role != 'view')
                    <li class="nav-item ">
                        <a class="nav-link" href="/order-admin/create-order"><i class="fa fa-sticky-note"></i>Create Order</a>
                    </li>
                    @endif
                    <li class="nav-item ">
                        <a class="nav-link" href="/order-admin/routes"><i class="fa fa-fw fa-user-circle"></i>Routs</span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="/order-admin/add-shop"><i class="fab fa-fw fa-wpforms"></i>Shops</a>
                    </li>
                    @if (Auth::user()->role != 'view')
                    <li class="nav-item">
                        <a class="nav-link" href="/order-admin/log"><i class="fas fa-fw fa-table"></i>Logs</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="/order-admin/export-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Export Report</a>
                        <a class="nav-link" href="/order-admin/shop-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Shop Report</a>
                        <a class="nav-link" href="/order-admin/final-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Final Report</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>