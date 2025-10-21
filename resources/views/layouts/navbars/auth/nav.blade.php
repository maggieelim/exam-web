<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-3 mx-4 shadow-none border-radius-xl" id="navbarBlur"
    navbar-scroll="true">
    <div class="container-fluid py-2 px-0 d-flex flex-wrap align-items-center justify-content-between">

        <!-- Breadcrumb + Title -->
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-2 mb-sm-0">
            <nav aria-label="breadcrumb" class="me-sm-3">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0">
                    <li class="breadcrumb-item text-sm">
                        <a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                    </li>
                    <li class="breadcrumb-item text-sm text-dark active text-capitalize" aria-current="page">
                        {{ getActiveSidebarTitle() }}
                    </li>
                </ol>
            </nav>
            <h6 class="font-weight-bolder mb-0 text-capitalize">{{ getActiveSidebarTitle() }}</h6>
        </div>

        <!-- Right Section (User Info + Dropdown) -->
        <div class="d-flex align-items-center">
            <ul class="navbar-nav d-flex flex-row align-items-center mb-0">
                <!-- User Dropdown - Desktop Only -->
                <li class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link d-flex align-items-center p-0" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-none d-sm-flex flex-column text-end me-2">
                            <span class="fw-bold text-sm">{{ Auth::user()->name }}</span>
                            <span class="text-muted text-xs">{{ Auth::user()->roles->pluck('name')->join(', ') }}</span>
                        </div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random"
                            alt="profile" class="avatar avatar-sm rounded-circle">
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ url('/logout') }}">
                                <i class="fa fa-sign-out text-danger"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Mobile View - Simple Logout Link -->
                <li class="nav-item d-lg-none">
                    <a class="nav-link d-flex align-items-center p-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random"
                            alt="profile" class="avatar avatar-sm rounded-circle">
                    </a>
                    <form id="logout-form-mobile" action="{{ url('/logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>

                <!-- Sidebar Toggle (for mobile only) -->
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center ms-2">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
