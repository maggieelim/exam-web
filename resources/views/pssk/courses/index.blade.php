@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    <h5 class="mb-0">List Blok</h5>
                    @if ($semesterId)
                    @php
                    $selectedSemester = $semesters->firstWhere('id', $semesterId);
                    @endphp

                    <x-semester-badge :semester="$selectedSemester" :activeSemester="$activeSemester" />
                    @endif
                </div>

                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    <a href="{{ route('courses.export', ['semester_id' => $semesterId]) }}"
                        class="btn btn-success btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    @role('admin')
                    <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                        +&nbsp; New Course
                    </a>
                    @endrole
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse {{ request()->hasAny(['semester_id', 'name', 'lecturer']) }}" id="filterCollapse">
                <form method="GET" action="{{ route('courses.index') }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <!-- Filter Semester (dari tabel semester) -->
                            <div class="col-md-6">
                                <label for="semester_id" class="form-label mb-1">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select">
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                        }}>
                                        {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                                        @if ($activeSemester && $semester->id == $activeSemester->id)
                                        (Active)
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Input Blok -->
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">Blok</label>
                                <input type="text" class="form-control form-control" name="name"
                                    value="{{ request('name') }}" placeholder="Kode atau nama blok">
                            </div>
                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('courses.index') }}" class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 text-wrap">
                        <thead>
                            <tr>
                                <x-sortable-th label="Kode Blok" field="kode_blok" :sort="$sort" :dir="$dir" />
                                <x-sortable-th label="Nama" field="name" :sort="$sort" :dir="$dir" />
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Semester
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                    Total
                                    Mahasiswa</th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($courses as $course)
                            <tr>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $course->kode_blok }}</span>
                                </td>
                                <td class="align-middle text-wrap">
                                    <span class="text-sm font-weight-bold">{{ $course->name }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $course->semester }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $course->student_count ?? 0 }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    @hasrole('admin')
                                    <a href="{{ route('courses.editKoor', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-user-cog me-2"></i>
                                        Koordinator
                                    </a>
                                    @endrole
                                    <a href="{{ route('courses.edit', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                        class="btn bg-gradient-info m-1 p-2 px-3" title="Info">
                                        <i class="fa-solid fa-pen me-2"></i>Jadwal
                                    </a>
                                    <button class="btn bg-gradient-warning dropdown-toggle m-1 p-2 px-3" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-cog me-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('attendances.report', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                                                <i class="fa-solid fa-file me-2"></i> Report Absensi
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('course.getAllPemicu', [$course->id, 'semester_id'=>$semesterId]) }}">
                                                <i class="fa-solid fa-star me-2"></i> Nilai Pemicu
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('courses.show', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                                                <i class="fas fa-info-circle me-2"></i> Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.courses.downloadAbsenSkillsLab', ['course' => $course->slug, 'semesterId' => $semesterId]) }}">
                                                <i class="fas fa-download me-2"></i> Download Form Absen
                                            </a>
                                        </li>
                                    </ul>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada course yang ditemukan</p>
                                        <a href="{{ route('courses.index') }}"
                                            class="btn btn-sm btn-outline-primary">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$courses" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush