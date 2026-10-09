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
                        <a class="nav-link active" href="{{route('rep.dashboard')}}"><i class="fa fa-fw fa-user-circle"></i>Dashboard <span class="badge badge-success">6</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-sticky-note"></i>Orders</a>
                        <div id="submenu-2" class="collapse submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('rep.pending-order')}}">Pending Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('rep.processing-order')}}">Procesing Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('rep.under-review-order')}}">Under Review Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('rep.complete-order')}}">Completed Orders</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{route('rep.create-order')}}"><i class="fa fa-sticky-note"></i>Create Order</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{route('rep.my-shops')}}"><i class="fab fa-fw fa-wpforms"></i>All My Shops</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/rep/export-report"><i class="fas fa-fw fa-table"></i>Export Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('rep.shop-report-morning')}}"><i class="fas fa-fw fa-table"></i>Shop Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('rep.final-report-morning')}}"><i class="fas fa-fw fa-table"></i>Final Report</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>