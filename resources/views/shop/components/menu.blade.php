<div class="nav-left-sidebar sidebar-dark">
    <div class="menu-list">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-divider">
                        Menu
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('shop/dashboard') ? 'active' : '' }}" href="/shop/dashboard"><i class="fa fa-fw fa-user-circle"></i>Dashboard <span class="badge badge-success">6</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop/all-orders*') ? 'active' : '' }}" href="/shop/all-orders"><i class="fa fa-sticky-note"></i>All Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop/pending-orders*') ? 'active' : '' }}" href="/shop/pending-orders"><i class="fa fa-sticky-note"></i>Pending Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop/processing-orders*') ? 'active' : '' }}" href="/shop/processing-orders"><i class="fa fa-sticky-note"></i>Procesing Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop/under-review-orders*') ? 'active' : '' }}" href="/shop/under-review-orders"><i class="fa fa-sticky-note"></i>Under Review Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop/complete-orders*') ? 'active' : '' }}" href="/shop/complete-orders"><i class="fa fa-sticky-note"></i>Completed Orders</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('shop/default-orders*') ? 'active' : '' }}" href="/shop/default-orders"><i class="fa fa-sticky-note"></i>Default Order</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link {{ request()->is('shop/create-order*', 'shop/cart*') ? 'active' : '' }}" href="/shop/create-order"><i class="fa fa-sticky-note"></i>Create Order</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
