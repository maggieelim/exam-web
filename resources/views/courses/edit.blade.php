@extends('layouts.user_type.auth')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="courseTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#kelas"
                                role="tab">KELAS</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#siswa"
                                role="tab">SISWA</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dosen"
                                role="tab">DOSEN</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#praktikum"
                                role="tab">PRAKTIKUM</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pemicu"
                                role="tab">PEMICU</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pleno"
                                role="tab">PLENO</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#skilllab" role="tab">SKILL
                                LAB</a></li>
                    </ul>
                </div>

                <div class="card-body px-4 pt-3 pb-2 tab-content">
                    <div class="tab-pane fade show active" id="kelas" role="tabpanel">
                        @include('courses.tabs._kelas')
                    </div>
                    <div class="tab-pane fade" id="siswa" role="tabpanel">
                        @include('courses.tabs._siswa')
                    </div>
                    <div class="tab-pane fade" id="dosen" role="tabpanel">
                        @include('courses.tabs._dosen')
                    </div>
                    <div class="tab-pane fade" id="praktikum" role="tabpanel">
                        @include('courses.tabs._praktikum')
                    </div>
                    <div class="tab-pane fade" id="pemicu" role="tabpanel">
                        @include('courses.tabs._pemicu')
                    </div>
                    <div class="tab-pane fade" id="pleno" role="tabpanel">
                        @include('courses.tabs._pleno')
                    </div>
                    <div class="tab-pane fade" id="skilllab" role="tabpanel">
                        @include('courses.tabs._skilllab')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
