@extends('layouts.user_type.auth')

@section('content')
@if ($semesterId)
@php
$selectedSemester = $semesters->firstWhere('id', $semesterId);
@endphp

<x-semester-badge :semester="$selectedSemester" :activeSemester="$activeSemester" />
@endif
<div class="d-flex justify-content-between pt-2 gap-2">
    <div>
        <h5 class="mb-0">List Blok</h5>
    </div>
    <div class="d-flex gap-2">
        @role('admin')
        <a href="{{ route('courses.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px;" title="Tambah {{ ucfirst($type ?? 'User') }}">
            <i class="fas fa-plus"></i>
        </a>
        @endrole
        <a href="{{ route('courses.export', ['semester_id' => $semesterId]) }}"
            class="btn btn-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
            title="Export Data">
            <i class="fas fa-download"></i>
        </a>
        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
            aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
            <i class="fas fa-filter"></i>
        </button>
    </div>
</div>
<div class="collapse {{ request()->hasAny(['semester_id', 'name', 'lecturer']) }}" id="filterCollapse">
    <form method="GET" action="{{ route('courses.index') }}">
        <div class="row g-2">
            <!-- Filter Semester (dari tabel semester) -->
            <div class="col-md-6">
                <label for="semester_id" class="form-label mb-1">Semester</label>
                <select name="semester_id" id="semester_id" class="form-select">
                    @foreach ($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : '' }}>
                        {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                        @if ($activeSemester && $semester->id == $activeSemester->id)
                        (Aktif)
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Blok -->
            <div class="col-md-6">
                <label for="blok" class="form-label mb-1">Blok</label>
                <input type="text" class="form-control form-control" name="name" value="{{ request('name') }}"
                    placeholder="Kode atau nama blok">
            </div>
            <!-- Buttons -->
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('courses.index') }}" class="btn btn-light btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
        </div>
    </form>
</div>

@forelse ($courses as $course)
<div class="col-lg-4 col-md-12 mb-4">
    <div class="card h-auto ">
        <div class="card-body p-3 pb-0">
            <div class="row">
                <div class="d-flex flex-column h-100">
                    {{-- Nama --}}
                    <h5 class="font-weight-bolder mb-1">{{ $course->kode_blok }}
                        {{ $course->name }}
                    </h5>
                    <p class="mb-1">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Semester: {{ $course->semester }}
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Total Lecturer: {{ $course->lecturer_count ?? 0 }}
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-user-graduate me-2"></i>
                        Total Students: {{ $course->student_count ?? 0 }}
                    </p>
                    {{-- Tombol Aksi --}}
                    <div class="my-auto pt-2">
                        <div class="d-flex gap-2">
                            <div class="flex-fill  btn-group">
                                <a class="btn  btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa-solid fa-pen me-1"></i> Edit
                                </a>

                                <ul class="dropdown-menu shadow">
                                    <li>
                                        @hasrole('admin')
                                        <a href="{{ route('courses.editKoor', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                            class="dropdown-item" title="Info">
                                            <i class="fas fa-user-cog me-2"></i>Koordinator
                                        </a>
                                        @endrole
                                        <a class="dropdown-item"
                                            href="{{ route('courses.edit', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                                            <i class="fas fa-cog me-2"></i>Atur Jadwal
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('attendances.report', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                                            <i class="fa-solid fa-file me-2"></i> Report Absensi
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('course.getAllPemicu', [$course->id, 'semester_id'=>$semesterId]) }}">
                                            <i class="fa-solid fa-star me-2"></i> Nilai Pemicu
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('courses.show', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                class="btn flex-fill btn-sm btn-outline-secondary" title="Lihat Detail">
                                <i class="fas fa-info-circle me-1"></i> Info
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="text-muted">
        <i class="fas fa-inbox fa-2x mb-2"></i>
        <p>Tidak ada course yang ditemukan</p>
        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-primary">Reset Filter</a>
    </div>
</div>
@endforelse
<div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$courses" />
</div>
@endsection
@push('dashboard')