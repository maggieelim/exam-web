@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    <h5 class="mb-0">{{ ucwords($status) }} Exams List</h5>
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
                    <a href="{{ route('exams.create') }}" class="btn bg-gradient-primary btn-sm" type="button">
                        + Add Exam test
                    </a>
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('exams.index', $status) }}">
                    <div class="mx-3 mb-2 pb-2">
                        <div class="row g-2">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <div class="col-md-4">
                                <label for="semester_id" class="form-label mb-1">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select">
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                        }}>
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
                                    <option value="{{ $course->id }}" {{ request('course_id')==$course->id ? 'selected'
                                        : '' }}>
                                        {{ $course->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('exams.index', $status) }}" class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive pb-4">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <x-sortable-th label="Exams" field="title" :sort="$sort" :dir="$dir" />
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Exam Date
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Exam Questions
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Duration
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($exams as $exam)
                            <tr>
                                <td class="align-middle px-3">
                                    <span class="text-sm font-weight-bold">
                                        {{ $exam->title }} <br>
                                        {{ $exam->course->name }} <br>
                                    </span>
                                    <span class="text-sm">
                                        Modified at:
                                        {{ \Carbon\Carbon::parse($exam->updated_at)->format('j/n/y H.i') }} by
                                        {{ $exam->updater->name }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">
                                        {{
                                        $exam->exam_date?\Carbon\Carbon::parse($exam->exam_date)->translatedFormat('l, j
                                        M Y ' ):'-'}}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">
                                        {{ $exam->questions_count > 0 ? $exam->questions_count . ' Questions' : 'No
                                        Questions Yet' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">
                                        {{ $exam->duration ? $exam->duration . ' Minutes' : 'No Duration Yet' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if ($exam->status === 'upcoming')
                                    <button type="button" class="btn bg-gradient-success m-1 p-2 px-3 start-exam-btn"
                                        data-exam-id="{{ $exam->id }}" data-exam-title="{{ $exam->title }}"
                                        data-action-url="{{ route('exams.start', $exam->id) }}">
                                        Start
                                    </button>
                                    @elseif($exam->status === 'ongoing')
                                    <button type="button" class="btn bg-gradient-danger m-1 p-2 px-3 end-exam-btn"
                                        title="End Exam" data-exam-id="{{ $exam->id }}"
                                        data-exam-title="{{ $exam->title }}"
                                        data-action-url="{{ route('exams.end', $exam->id) }}">
                                        End
                                    </button>
                                    <a href="{{ route('exams.ongoing', [$exam->exam_code]) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Exam Participants">
                                        <i class="fas fa-users me-1"></i> </a>
                                    @else
                                    <span class="badge bg-secondary">Ended</span>
                                    @endif
                                    <div class="btn-group">
                                        <button class="btn bg-gradient-warning dropdown-toggle m-1 p-2 px-3"
                                            type="button" id="examManagementDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-cog me-1"></i>
                                        </button>
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
                                        </ul>
                                    </div>
                                    <a href="{{ route('exams.show.' . $status, [$exam->exam_code]) }}"
                                        class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada Exam yang ditemukan</p>
                                        <a href="{{ route('exams.index', $status) }}"
                                            class="btn btn-sm btn-outline-primary">Reset
                                            Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$exams" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="startExamModal" tabindex="-1" aria-labelledby="startExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startExamModalLabel">Konfirmasi Mulai Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin memulai ujian <strong id="startExamTitle"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="startExamForm" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-success">Ya, Mulai Ujian</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi End (Satu untuk semua) -->
    <div class="modal fade" id="endExamModal" tabindex="-1" aria-labelledby="endExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="endExamModalLabel">Konfirmasi Akhiri Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin mengakhiri ujian <strong id="endExamTitle"></strong>?</p>
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Peringatan:</strong> Setelah ujian diakhiri:
                    <ul class="mb-0">
                        <li>Siswa tidak dapat lagi mengerjakan ujian</li>
                        <li>Semua attempt yang sedang berjalan akan otomatis diselesaikan</li>
                        <li>Tindakan ini tidak dapat dibatalkan</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="endExamForm" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-danger">Ya Akhiri Ujian</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.start-exam-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const examId = this.getAttribute('data-exam-id');
                        const examTitle = this.getAttribute('data-exam-title');
                        const actionUrl = this.getAttribute('data-action-url');

                        // Update modal content
                        document.getElementById('startExamTitle').textContent = examTitle;
                        document.getElementById('startExamForm').action = actionUrl;

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('startExamModal'));
                        modal.show();
                    });
                });

                // Handle End Exam buttons
                document.querySelectorAll('.end-exam-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const examId = this.getAttribute('data-exam-id');
                        const examTitle = this.getAttribute('data-exam-title');
                        const actionUrl = this.getAttribute('data-action-url');

                        // Update modal content
                        document.getElementById('endExamTitle').textContent = examTitle;
                        document.getElementById('endExamForm').action = actionUrl;

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('endExamModal'));
                        modal.show();
                    });
                });
            });
    </script>
    @endsection
    @push('dashboard')