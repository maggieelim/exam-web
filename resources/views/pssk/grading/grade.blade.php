@extends('layouts.user_type.auth')

@section('content')
<div class="col-12">
    <div class="card mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-3">{{ $exam->title }}</h5>
            <div>
                @if ($exam->is_published)
                <button type="button" class="btn btn-sm btn-success" disabled>Published</button>
                @else
                <form role="form" action="{{ route('lecturer.results.publish', [ $exam->exam_code]) }}" method="POST"
                    class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info px-3 py-2"
                        onclick="return confirm('Yakin ingin publish exam ini?')">
                        Publish Exam
                    </button>
                </form>
                @endif
                <a href="{{ route('lecturer.results.download', $exam->exam_code) }}"
                    class="btn btn-warning px-3 py-2"><i class="fas fa-download"></i>
                    <span class="d-none d-md-inline ms-1">Download</span>
                </a>
            </div>
        </div>
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
                    @foreach ($lecturers as $lecturer)
                    <li>{{ $lecturer->lecturer->user->name}}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Card Daftar Mahasiswa -->
    <div class="d-flex justify-content-between mb-0 pb-0">
        <h5>List Students</h5>
        <button class="btn px-3 py-2 btn-outline-secondary" type="button" data-bs-toggle="collapse"
            data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> <span class="d-none d-md-inline ms-1">Filter</span>
        </button>
    </div>
    <div class="collapse" id="filterCollapse">
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
    <div class="px-0 pt-0 pb-2">
        <!-- MOBILE VIEW -->
        <div class="row d-block d-md-none">
            @forelse ($results as $result)
            <div class="col-12 mb-3">
                <div class="card h-auto">
                    <div class="card-body p-3 pb-2">
                        <p class="mb-1 fw-bold">
                            Name: {{ $result['student']->name ?? $result['student']['name'] ?? '-' }}
                        </p>
                        <p class="mb-1 fw-bold">
                            NIM: {{ $result['student_data']->nim ?? $result['student']['nim'] ?? $result['nim'] ?? '-'
                            }}
                        </p>
                        <p class="mb-2 fw-bold">
                            Total Answered:
                            {{ $result['total_answered'] ?? 0 }}/{{ $exam->questions_count }}
                        </p>

                        <div class="category-container mb-2" style="max-height: 120px; overflow-y: auto;">
                            @forelse ($result['categories_result'] ?? [] as $cat)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-light text-dark me-2"
                                    style="min-width: 110px; font-size: 0.75rem;">
                                    {{ Str::limit($cat['category_name'] ?? 'Uncategorized', 18) }}
                                </span>

                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar
                                        @php $percentage = $cat['percentage'] ?? 0; @endphp
                                        @if ($percentage == 0) bg-secondary opacity-50
                                        @elseif($percentage >= 80) bg-success
                                        @elseif($percentage >= 60) bg-info
                                        @elseif($percentage >= 40) bg-warning
                                        @else bg-danger @endif" style="width: {{ max($percentage, 1) }}%">
                                    </div>
                                </div>

                                <small class="ms-2 text-muted" style="min-width: 35px;">
                                    {{ $percentage }}%
                                </small>
                            </div>
                            @empty
                            <p class="text-muted text-center">No category data available</p>
                            @endforelse
                        </div>

                        <a href="{{ route('lecturer.feedback.' . $status, [
                        'exam_code' => $exam->exam_code,
                        'nim' => $result['student_data']->nim ?? $result['student']['nim'] ?? $result['nim'] ?? ''
                    ]) }}" class="btn btn-sm w-100 bg-gradient-warning">
                            <i class="fas fa-comment"></i> Feedback
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted">No results found</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- DESKTOP VIEW -->
        <div class="d-none d-md-block">
            <div class="card">
                <div class="card-body pt-0 table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    <a
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'nim', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                        NIM
                                        @if (request('sort') === 'nim')
                                        <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    <a
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                        Name
                                        @if (request('sort') === 'name')
                                        <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-uppercase text-wrap text-dark text-sm font-weight-bolder">
                                    Answered Questions
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Score
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    <a
                                        href="{{ request()->fullUrlWithQuery(['sort' => 'feedback', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                        Feedback
                                        @if (request('sort') === 'feedback')
                                        <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $result)
                            <tr>
                                <td class="align-middle text-sm text-center">
                                    {{ $result['student_data']->nim ?? $result['student']['nim'] ?? $result['nim'] ??
                                    '-' }}
                                </td>
                                <td class="align-middle text-sm w-70 text-wrap text-center">
                                    {{ $result['student']->name ?? $result['student']['name'] ?? '-' }}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    {{ $result['total_answered'] ?? 0 }}/{{ $exam->questions_count }}
                                </td>
                                <td class="align-middle">
                                    <div class="category-container" style="overflow-y: auto;">
                                        @forelse ($result['categories_result'] ?? [] as $cat)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-light text-dark me-2"
                                                style="min-width: 120px; font-size: 0.75rem;">
                                                {{ Str::limit($cat['category_name'] ?? 'Uncategorized', 20) }}
                                            </span>
                                            <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
                                                @php $percentage = $cat['percentage'] ?? 0; @endphp
                                                <div class="progress-bar m-0 
                                                    @if ($percentage == 0) 
                                                        bg-secondary opacity-50 
                                                    @elseif($percentage >= 80) 
                                                        bg-success 
                                                    @elseif($percentage >= 60) 
                                                        bg-info 
                                                    @elseif($percentage >= 40) 
                                                        bg-warning 
                                                    @else 
                                                        bg-danger 
                                                    @endif" role="progressbar"
                                                    style="width: {{ max($percentage, 1) }}%" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ $percentage }}% - {{ $cat['total_correct'] ?? 0 }}/{{ $cat['total_question'] ?? 0 }} correct">
                                                </div>
                                            </div>
                                            <small class="ms-2 text-muted" style="min-width: 40px;">
                                                {{ $percentage }}%
                                            </small>
                                        </div>
                                        @empty
                                        <div class="text-muted text-center">No data</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="align-middle text-sm text-center">
                                    {{ $result['feedback'] ?? 0 }}
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('lecturer.feedback.' . $status, [
                                        'exam_code' => $exam->exam_code, 
                                        'nim' => $result['student_data']->nim ?? $result['student']['nim'] ?? $result['nim'] ?? ''
                                    ]) }}" class="btn bg-gradient-warning m-1 p-2 px-3" title="Feedback">
                                        <i class="fas fa-comment"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No results found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    <x-pagination :paginator="$attempts" />
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection