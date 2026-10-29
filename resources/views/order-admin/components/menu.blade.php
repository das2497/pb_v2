@php
    $ordersActive = request()->is(
        'order-admin/pending-orders*',
        'order-admin/processing-orders*',
        'order-admin/under-review-orders*',
        'order-admin/complete-orders*',
        'order-admin/under-complete-orders-view*',
    );
    $repActive = request()->is('order-admin/add-rep*', 'order-admin/rep-assign*');
@endphp
<div class="nav-left-sidebar sidebar-dark">
    <div class="menu-list">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-divider">
                        Menu
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('order-admin/dashboard') ? 'active' : '' }}" href="/order-admin/dashboard"><i class="fa fa-fw fa-user-circle"></i>Dashboard <span class="badge badge-success">6</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $ordersActive ? 'active' : '' }}" href="#" data-toggle="collapse" aria-expanded="{{ $ordersActive ? 'true' : 'false' }}" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-sticky-note"></i>Orders</a>
                        <div id="submenu-2" class="collapse submenu {{ $ordersActive ? 'show' : '' }}">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/pending-orders*') ? 'active' : '' }}" href="/order-admin/pending-orders">Pending Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/processing-orders*') ? 'active' : '' }}" href="/order-admin/processing-orders">Procesing Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/under-review-orders*') ? 'active' : '' }}" href="/order-admin/under-review-orders">Under Review Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/complete-orders*', 'order-admin/under-complete-orders-view*') ? 'active' : '' }}" href="/order-admin/complete-orders">Completed Orders</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @if (Auth::user()->role != 'view')
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('order-admin/create-order*', 'order-admin/cart*') ? 'active' : '' }}" href="/order-admin/create-order"><i class="fa fa-sticky-note"></i>Create Order</a>
                    </li>
                    @endif
                    @if (Auth::user()->role != 'sales_admin')
                    <li class="nav-item">
                        <a class="nav-link {{ $repActive ? 'active' : '' }}" href="#" data-toggle="collapse" aria-expanded="{{ $repActive ? 'true' : 'false' }}" data-target="#submenu-3" aria-controls="submenu-3"><i class="fas fa-fw fa-chart-pie"></i>Representative</a>
                        <div id="submenu-3" class="collapse submenu {{ $repActive ? 'show' : '' }}">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/add-rep*') ? 'active' : '' }}" href="/order-admin/add-rep">Representatives</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('order-admin/rep-assign*') ? 'active' : '' }}" href="/order-admin/rep-assign">Assign Shops</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('order-admin/routes*') ? 'active' : '' }}" href="/order-admin/routes"><i class="fa fa-fw fa-user-circle"></i>Routs</span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('order-admin/add-shop*') ? 'active' : '' }}" href="/order-admin/add-shop"><i class="fab fa-fw fa-wpforms"></i>Shops</a>
                    </li>
                    @if (Auth::user()->role != 'sales_admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('order-admin/add-products*') ? 'active' : '' }}" href="/order-admin/add-products"><i class="fas fa-fw fa-table"></i>Products</a>
                    </li>
                    @endif
                    @if (Auth::user()->role != 'view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('order-admin/log*') ? 'active' : '' }}" href="/order-admin/log"><i class="fas fa-fw fa-table"></i>Logs</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('order-admin/export-report*') ? 'active' : '' }}" href="/order-admin/export-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Export Report</a>
                        <a class="nav-link {{ request()->is('order-admin/shop-report*', 'order-admin/shop-normal-report*') ? 'active' : '' }}" href="/order-admin/shop-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Shop Report</a>
                        <a class="nav-link {{ request()->is('order-admin/final-report*') ? 'active' : '' }}" href="/order-admin/final-report" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Final Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('order-admin/scheduled-production-report*') ? 'active' : '' }}" href="/order-admin/scheduled-production-report"><i class="fas fa-fw fa-calendar-alt"></i>Scheduled Production</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
