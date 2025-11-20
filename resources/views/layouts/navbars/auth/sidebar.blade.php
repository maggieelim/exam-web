<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-0 fixed-start"
    id="sidenav-main" style="height: 100vh;">
    <div class="sidenav-header text-center">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex justify-content-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/img/Logo-kedokteran-untar.png') }}" class="navbar-brand-img h-100" alt="Logo">
        </a>
    </div>

    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto h-100 mb-4" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- <li class="nav-item">
        <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ url('dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-tachometer-alt {{ Request::is('dashboard') ? 'text-white' : 'text-dark' }}"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li> -->

            @role('admin')
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">User Management</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users/student*') ? 'active' : '' }}"
                    href="{{ url('admin/users/student') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-user-graduate {{ request()->is('admin/users/student*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users/lecturer*') ? 'active' : '' }}"
                    href="{{ url('admin/users/lecturer') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-chalkboard-teacher {{ request()->is('admin/users/lecturer*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Lecturers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users/admin*') ? 'active' : '' }}"
                    href="{{ url('admin/users/admin') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-user-shield {{ request()->is('admin/users/admin*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Admin</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Course</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('course*') ? 'active' : '' }}" href="{{ url('courses') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book {{ request()->is('course*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manage Courses</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/semester*') ? 'active' : '' }}"
                    href="{{ url('admin/semester') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-calendar {{ request()->is('admin/semester*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Academic Year</span>
                </a>
            </li>
            @endrole
            @hasanyrole([ 'lecturer'])
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Assessment</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('attendance*') && !request()->is('attendances/report*') ? 'active' : '' }}"
                    href="{{ url('attendance') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-clipboard-check {{ request()->is('attendance*') && !request()->is('attendances/report*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Attendance</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('tutor*') ? 'active' : '' }}" href="{{ url('tutors') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-star {{ request()->is('tutor*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Nilai Tutor</span>
                </a>
            </li>
            @endhasanyrole
            @hasrole('koordinator')
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Course</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('courses*') ? 'active' : '' }}" href="{{ url('courses') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book {{ request()->is('courses*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manage Courses</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Exams</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('exams/upcoming*') ? 'active' : '' }}"
                    href="{{ url('exams/upcoming') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-calendar-alt {{ request()->is('exams/upcoming*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Upcoming Exam</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('exams/ongoing*') ? 'active' : '' }}"
                    href="{{ url('exams/ongoing') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-clipboard-list {{ request()->is('exams/ongoing*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Ongoing Exams</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('exams/previous*') ? 'active' : '' }}"
                    href="{{ url('exams/previous') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-history {{ request()->is('exams/previous*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Previous Exam</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Exams Report</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('ungraded*') ? 'active' : '' }}" href="{{ url('ungraded') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-clipboard-list {{ request()->is('ungraded*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Ungraded</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('published*') ? 'active' : '' }}" href="{{ url('published') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-clipboard-list {{ request()->is('published*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Published</span>
                </a>
            </li>
            @endrole

            @role('student')
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Exams</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('student/exams/upcoming') ? 'active' : '' }}"
                    href="{{ url('student/exams/upcoming') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-calendar-alt {{ request()->is('student/exams/upcoming') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Upcoming Exam</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('student/exams/previous*') ? 'active' : '' }}"
                    href="{{ url('student/exams/previous') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i
                            class="fas fa-history {{ request()->is('student/exams/previous*') ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Previous Exam</span>
                </a>
            </li>
            @endrole
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/logout') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sign Out</span>
                </a>
            </li>
        </ul>
    </div>
</aside>