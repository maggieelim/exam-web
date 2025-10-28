@extends('layouts.user_type.auth')

@section('content')
    <div class="d-flex gap-2 justify-content-end pt-0 mt-0">
        @if ($exam->is_published)
            <button type="button" class="btn btn-sm btn-success" disabled>Published</button>
        @else
            <form role="form" action="/lecturer/{{ $exam->exam_code }}/publish" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Yakin ingin publish exam ini?')">
                    Publish Exam
                </button>
            </form>
        @endif
        <a href="{{ route('lecturer.results.download', $exam->exam_code) }}" class="btn btn-sm btn-warning"><i
                class="fas fa-download"></i>
            Download
        </a>
    </div>
    <div class="card mb-4 p-3">
        <h5 class="mb-3">{{ $exam->title }}</h5>
        <div class="row">
            <div class="col-md-4">
                <p><strong>Blok:</strong> {{ $exam->course->name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Semester:</strong> {{ $exam->semester->semester_name }}
                    {{ $exam->semester->academicYear->year_name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Tanggal:</strong> {{ $exam->exam_date->format('d-m-Y') }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Durasi:</strong> {{ $exam->duration }} minutes</p>
            </div>
            <div class="col-md-4">
                <p><strong>Total mahasiswa:</strong> {{ $exam->attempts_count }}</p>
            </div>
            <div class="col-md-4 d-flex">
                <strong class="me-3">Dosen:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($exam->course->lecturers as $lecturer)
                        <li>{{ $lecturer->user->name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <h5 class="mb-2">List Students</h5>
        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
            aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
            <i class="fas fa-filter"></i>
        </button>
    </div>

    <div class="collapse card mb-3" id="filterCollapse">
        <form method="GET" action="{{ route('lecturer.grade.' . $status, $exam->exam_code) }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="mx-3">
                <div class="row g-2">
                    <!-- Input Blok -->
                    <div class="col-md-12">
                        <label for="blok" class="form-label mb-1">NIM/Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Search Name or NIM"
                            value="{{ request('name') }}">
                    </div>
                    <!-- Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('lecturer.grade.' . $status, $exam->exam_code) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @foreach ($results as $result)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-auto ">
                <div class="card-body p-3 pb-0">
                    <p class="mb-1 fw-bold">Name:
                        {{ $result['student']['name'] }}
                    </p>
                    <p class="mb-1 fw-bold">NIM:
                        {{ $result['student']['student']['nim'] }} </p>
                    <p class="mb-1 fw-bold">Total Answered:
                        {{ $result['total_answered'] }}/{{ $exam->questions_count }}</td>
                    </p>
                    <div class="category-container" style="max-height: 120px; overflow-y: auto;">
                        @foreach ($result['categories_result'] as $cat)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-light text-dark me-2" style="min-width: 120px; font-size: 0.75rem;">
                                    {{ Str::limit($cat['category_name'], 20) }}
                                </span>
                                <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
                                    <div class="progress-bar m-0
                                            @if ($cat['percentage'] == 0) bg-secondary opacity-50
                                            @elseif($cat['percentage'] >= 80) bg-success
                                            @elseif($cat['percentage'] >= 60) bg-info
                                            @elseif($cat['percentage'] >= 40) bg-warning
                                            @else bg-danger @endif"
                                        role="progressbar" style="width: {{ max($cat['percentage'], 1) }}%"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $cat['percentage'] }}% - {{ $cat['total_correct'] }}/{{ $cat['total_question'] }} correct">
                                    </div>
                                </div>
                                <small class="ms-2 text-muted" style="min-width: 40px;">
                                    {{ $cat['percentage'] }}%
                                </small>
                            </div>
                        @endforeach
                    </div>
                    <div class="my-auto pt-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('lecturer.feedback.' . $status, ['exam_code' => $exam->exam_code, 'nim' => $result['student']['student']['nim']]) }}"
                                class="btn flex-fill bg-gradient-warning" title="Feedback">
                                <i class="fas fa-comment"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$attempts" />
    </div>
@endsection
@push('dashboard')
