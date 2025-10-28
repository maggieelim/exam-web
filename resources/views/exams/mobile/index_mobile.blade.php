@extends('layouts.user_type.auth')

@section('content')
    <div class="d-flex justify-content-between pt-2 gap-2">
        <div>
            <h5 class="mb-0">Exams List</h5>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('exams.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center"
                style="height: 32px;">
                <i class="fas fa-plus"></i> New Exam
            </a>
            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>
    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('exams.index', $status) }}">
            <div class="mx-3 mb-2 pb-2">
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
                        <a href="{{ route('exams.index', $status) }}" class="btn btn-light">Reset</a>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </div>
            </div>
        </form>

    </div>

    @forelse ($exams as $exam)
        <div class="col-lg-4 col-md-6 mb-4">
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
                            <p class="mb-1">
                                <i class="fas fa-file-alt me-2"></i>
                                Total Questions:
                                {{ $exam->questions_count > 0 ? $exam->questions_count . ' Questions' : 'No Questions Yet' }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-clock me-2"></i>
                                Exam Duration: {{ $exam->duration . ' Minutes' }}
                            </p>
                            {{-- Tombol Aksi --}}
                            <div class="my-auto pt-2">
                                <div class="d-flex gap-2">
                                    @if ($exam->status === 'upcoming')
                                        <button type="button" class="btn bg-gradient-success flex-fill start-exam-btn"
                                            data-exam-id="{{ $exam->id }}" data-exam-title="{{ $exam->title }}"
                                            data-action-url="{{ route('exams.start', $exam->id) }}">
                                            Start
                                        </button>
                                    @elseif($exam->status === 'ongoing')
                                        <button type="button" class="btn bg-gradient-danger  end-exam-btn"
                                            title= "End Exam" data-exam-id="{{ $exam->id }}"
                                            data-exam-title="{{ $exam->title }}"
                                            data-action-url="{{ route('exams.end', $exam->id) }}">
                                            End
                                        </button>
                                        <a href="{{ route('exams.ongoing', [$exam->exam_code]) }}"
                                            class="btn bg-gradient-primary " title="Exam Participants">
                                            <i class="fas fa-users"></i> </a>
                                    @else
                                        <a class="btn flex-fill bg-secondary text-white">Ended</a>
                                    @endif
                                    <div class="flex-fill  btn-group">
                                        <a class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fa-solid fa-pen "></i>
                                        </a>
                                        <ul class="dropdown-menu shadow" aria-labelledby="examManagementDropdown">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('exams.edit', [$status, $exam->exam_code]) }}">
                                                    <i class="fas fa-cog text-primary me-2"></i> Exam Settings
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('exams.questions.' . $status, $exam->exam_code) }}">
                                                    <i class="fas fa-edit text-warning me-2"></i> Manage Questions
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('courses.editStudent', ['slug' => $exam->course->slug, 'semester_id' => $semesterId]) }}">
                                                    <i class="fas fa-users text-info me-2"></i> Manage Participants
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a href="{{ route('exams.show.' . $status, [$exam->exam_code]) }}"
                                        class="btn flex-fill btn-outline-secondary" title="Lihat Detail">
                                        <i class="fas fa-info-circle "></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card d-flex align-items-center justify-content-center text-center" style="min-height: 200px;">
            <div class="text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Tidak ada Exam yang ditemukan</p>
                <a href="{{ route('exams.index', $status) }}" class="btn btn-outline-primary">Reset Filter</a>
            </div>
        </div>
    @endforelse
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$exams" />
    </div>
@endsection
@push('dashboard')
