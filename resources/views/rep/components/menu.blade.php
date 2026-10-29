@php
    $ordersActive = request()->is(
        'rep/pending-order*',
        'rep/processing-order*',
        'rep/under-review-order*',
        'rep/complete-order*',
        'rep/under-complete-orders-view*',
    );
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
                        <a class="nav-link {{ request()->is('rep/dashboard') ? 'active' : '' }}" href="{{route('rep.dashboard')}}"><i class="fa fa-fw fa-user-circle"></i>Dashboard <span class="badge badge-success">6</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $ordersActive ? 'active' : '' }}" href="#" data-toggle="collapse" aria-expanded="{{ $ordersActive ? 'true' : 'false' }}" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-sticky-note"></i>Orders</a>
                        <div id="submenu-2" class="collapse submenu {{ $ordersActive ? 'show' : '' }}">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('rep/pending-order*') ? 'active' : '' }}" href="{{route('rep.pending-order')}}">Pending Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('rep/processing-order*') ? 'active' : '' }}" href="{{route('rep.processing-order')}}">Procesing Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('rep/under-review-order*') ? 'active' : '' }}" href="{{route('rep.under-review-order')}}">Under Review Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('rep/complete-order*', 'rep/under-complete-orders-view*') ? 'active' : '' }}" href="{{route('rep.complete-order')}}">Completed Orders</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('rep/create-order*', 'rep/cart*') ? 'active' : '' }}" href="{{route('rep.create-order')}}"><i class="fa fa-sticky-note"></i>Create Order</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('rep/my-shops*') ? 'active' : '' }}" href="{{route('rep.my-shops')}}"><i class="fab fa-fw fa-wpforms"></i>All My Shops</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('rep/export-report*') ? 'active' : '' }}" href="/rep/export-report"><i class="fas fa-fw fa-table"></i>Export Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('rep/shop-report*') ? 'active' : '' }}" href="{{route('rep.shop-report-morning')}}"><i class="fas fa-fw fa-table"></i>Shop Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('rep/final-report*') ? 'active' : '' }}" href="{{route('rep.final-report-morning')}}"><i class="fas fa-fw fa-table"></i>Final Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('rep/scheduled-production-report*') ? 'active' : '' }}" href="{{route('rep.scheduled-production-report')}}"><i class="fas fa-fw fa-calendar-alt"></i>Scheduled Production</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
