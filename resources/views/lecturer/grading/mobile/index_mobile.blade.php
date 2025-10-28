@extends('layouts.user_type.auth')

@section('content')
    <div class="d-flex justify-content-between pt-2 gap-2">
        <div>
            <h5 class="mb-0">Exams List</h5>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>

    <!-- Collapse Form -->
    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('lecturer.results.index', $status) }}">
            <div class="mx-3 my-2 py-2">
                <div class="row g-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="col-md-4">
                        <label for="semester_id" class="form-label mb-1">Semester</label>
                        <select name="semester_id" id="semester_id" class="form-select">
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                                    @if ($activeSemester && $semester->id == $activeSemester->id)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="title" class="form-label mb-1">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Cari Judul Ujian"
                            value="{{ request('title') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="blok" class="form-label mb-1">Blok</label>
                        <select name="course_id" class="form-control">
                            <option value="">-- Pilih Course --</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}"
                                    {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('lecturer.results.index', $status) }}" class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @foreach ($exams as $exam)
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-auto ">
                <div class="card-body p-3 pb-0">
                    <div class="row">
                        <div class="d-flex flex-column h-100">
                            {{-- Nama --}}
                            <h5 class="font-weight-bolder mb-0">
                                {{ $exam->title }}
                            </h5>
                            <h6 class="font-weight-bolder mb-1">
                                {{ $exam->course->name }} </h6>
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Modified at:
                                {{ \Carbon\Carbon::parse($exam->updated_at)->format('j/n/y H.i') }} by
                                {{ $exam->updater->name }}
                            </p>
                            {{-- Tombol Aksi --}}
                            <div class="my-auto pt-2">
                                <div class="d-flex gap-2">
                                    @if ($exam->is_published)
                                        <a href="{{ route('lecturer.grade.published', [$exam->exam_code]) }}"
                                            class="btn flex-fill bg-gradient-success" title="Info">
                                            Graded </a>
                                        <a href="{{ route('lecturer.results.show.published', [$exam->exam_code]) }}"
                                            class="btn flex-fill bg-gradient-primary" title="Info">
                                            <i class="fas fa-chart-line"></i> </a>
                                    @else
                                        <a href="{{ route('lecturer.grade.ungraded', [$exam->exam_code]) }}"
                                            class="btn flex-fill bg-gradient-info " title="Info">
                                            Grade </a>
                                        <a href="{{ route('lecturer.results.show.ungraded', [$exam->exam_code]) }}"
                                            class="btn flex-fill bg-gradient-primary" title="Info">
                                            <i class="fas fa-chart-line"></i> </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$exams" />
    </div>
@endsection
@push('dashboard')
