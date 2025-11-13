@extends('layouts.user_type.auth')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-2">
                    <h4 class="text-uppercase">PENJADWALAN PSSK - Blok {{ $course->name }}</h4>

                    <ul class="nav nav-tabs card-header-tabs" id="courseTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'kelas' ? 'active' : '' }}"
                                href="?tab=kelas&semester_id={{ $semesterId }}">
                                KELAS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'siswa' ? 'active' : '' }}"
                                href="?tab=siswa&semester_id={{ $semesterId }}">
                                SISWA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'dosen' ? 'active' : '' }}"
                                href="?tab=dosen&semester_id={{ $semesterId }}">
                                DOSEN
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'praktikum' ? 'active' : '' }}"
                                href="?tab=praktikum&semester_id={{ $semesterId }}">
                                PRAKTIKUM
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'pemicu' ? 'active' : '' }}"
                                href="?tab=pemicu&semester_id={{ $semesterId }}">
                                PEMICU
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'pleno' ? 'active' : '' }}"
                                href="?tab=pleno&semester_id={{ $semesterId }}">
                                PLENO
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'skilllab' ? 'active' : '' }}"
                                href="?tab=skilllab&semester_id={{ $semesterId }}">
                                SKILL LAB
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body px-4 pt-3 pb-2">
                    @switch($activeTab)
                        @case('kelas')
                            @include('courses.tabs._kelas')
                        @break

                        @case('siswa')
                            @include('courses.tabs._siswa')
                        @break

                        @case('dosen')
                            @include('courses.tabs._dosen')
                        @break

                        @case('praktikum')
                            @include('courses.tabs._praktikum')
                        @break

                        @case('pemicu')
                            @include('courses.tabs._pemicu')
                        @break

                        @case('pleno')
                            @include('courses.tabs._pleno')
                        @break

                        @case('skilllab')
                            @include('courses.tabs._skilllab')
                        @break

                        @default
                            @include('courses.tabs._kelas')
                    @endswitch
                </div>
            </div>
        </div>
    </div>
@endsection
