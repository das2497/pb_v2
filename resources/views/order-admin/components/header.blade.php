<!-- loading animation -->
<link rel="stylesheet" href="{{ asset('assets/libs/css/loading.css') }}">

<!-- snow animation -->
<!-- <link rel="stylesheet" href="{{ asset('assets/libs/css/snow.css') }}"> -->

<!-- Snowfall Animation Container -->
<!-- <div class="snow-container" id="snowContainer"></div> -->

<div id="preloader">
    <div id="preloader">
        <div class="loading-container">
            <svg class="loader" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 340 340">
                <circle cx="170" cy="170" r="160" stroke="#E2007C" />
                <circle cx="170" cy="170" r="135" stroke="#404041" />
                <circle cx="170" cy="170" r="110" stroke="#E2007C" />
                <circle cx="170" cy="170" r="85" stroke="#404041" />
            </svg>
        </div>
    </div>
</div>

<!-- Loading JS -->
<script src="{{ asset('assets/libs/js/loading.js') }}"></script>

<!-- snow animation -->
<!-- <script src="{{ asset('assets/libs/js/snow.js') }}"></script> -->

<div class="dashboard-main-wrapper">
    <div class="dashboard-header">
        <nav class="navbar navbar-expand-lg bg-white fixed-top">
            <a class="navbar-brand" href="/order-admin/dashboard"><img src="../assets/images/logo.png" width="32" alt=""> {{Auth::user()->name}}</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                </svg>
            </button>
            <div class="collapse navbar-collapse " id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto navbar-right-top">
                    <li class="nav-item dropdown nav-user">
                        <a class="nav-link nav-user-img" href="#" id="navbarDropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../assets/images/logo.png" alt="" class="user-avatar-md rounded-circle"></a>
                        <div class="dropdown-menu dropdown-menu-right nav-user-dropdown" aria-labelledby="navbarDropdownMenuLink2">
                            <div class="nav-user-info">
                                <h5 class="mb-0 text-white nav-user-name">{{auth()->user()->name}}</h5>
                            </div>
                            <a class="dropdown-item" href="{{route('order-admin.profile')}}"><i class="fas fa-user mr-2"></i>Profile Settings</a>
                            <div class="dropdown-item text-center">
                                <form action="{{route('logout')}}" method="POST">@csrf
                                    <button type="submit" class="btn btn-outline-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<script>
    // Store the scroll position before the page unloads
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
    });

    // Restore the scroll position when the page loads
    window.addEventListener('load', function() {
        const scrollPosition = sessionStorage.getItem('scrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition, 10));
            sessionStorage.removeItem('scrollPosition'); // Optionally clear the stored position
        }
    });
</script>