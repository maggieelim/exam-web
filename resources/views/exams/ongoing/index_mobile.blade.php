@extends('layouts.user_type.auth')

@section('content')
    <div class="card mb-3 p-3">
        <h5 class="card-title mb-2">
            <i class="fas fa-chart-bar me-2"></i>{{ $exam['title'] ?? 'Exam Title' }} - Ongoing Participants
        </h5>
        <div class="card-body p-0 m-0 d-flex justify-content-between">
            <div class="col-4 m-0 p-0">
                <div class="text-center">
                    <h3 class="text-primary mb-1" style="font-size: 1.3rem;">{{ $stats['total_participants'] ?? 0 }}
                    </h3>
                    <p class="text-muted mb-0 small">Total</p>
                </div>
            </div>
            <div class="col-4 m-0 p-0">
                <div class="text-center">
                    <h3 class="text-primary mb-1" style="font-size: 1.3rem;">{{ $stats['active_participants'] ?? 0 }}
                    </h3>
                    <p class="text-muted mb-0 small">Active</p>
                </div>
            </div>
            <div class="col-4 m-0 p-0">
                <div class="text-center">
                    <h3 class="text-info mb-1" style="font-size: 1.3rem;">{{ $stats['completed_participants'] ?? 0 }}
                    </h3>
                    <p class="text-muted mb-0 small">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('exams.index', $status = 'ongoing') }}" class="btn btn-sm btn-secondary">Back</a>
        <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
            aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
            <i class="fas fa-filter"></i>
        </button>
    </div>
    <div class="collapse card mb-3" id="filterCollapse">
        <form method="GET" action="{{ route('exams.ongoing', $exam->exam_code) }}">
            <div class="mx-3 row g-2">
                <div class="col-md-6">
                    <label for="search" class="form-label mb-1">NIM/Name</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari NIM/Name"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label mb-1">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">-- All Status --</option>
                        @foreach ($availableStatuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('exams.ongoing', $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </div>
        </form>
    </div>

    @foreach ($attempts as $attempt)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-auto ">
                <div class="card-body p-3 ">
                    <div class="row">
                        <div class="d-flex flex-column h-100">
                            <p class="mb-1">
                                <i class="fas fa-user me-2"></i>
                                Name:
                                {{ $attempt['student_name'] }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-id-card me-2"></i>
                                NIM:
                                {{ $attempt['nim'] }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-list-ol me-2"></i>
                                Questions Answered:
                                {{ $attempt['answered_count'] }}/ {{ $attempt['total_questions'] }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-info-circle me-2"></i>
                                Status: <span class="badge {{ $attempt['status_badge']['class'] }}">
                                    {{ $attempt['status_badge']['text'] }}
                                </span>
                            </p>
                            {{-- Tombol Aksi --}}
                            <div class="my-auto pt-2">
                                <div class="d-flex gap-2">
                                    @if ($attempt['status'] === 'completed' || $attempt['status'] === 'timeout')
                                        <a href="{{ route('exams.retake', [$exam->exam_code, $attempt['id']]) }}"
                                            class="btn flex-fill bg-gradient-warning m-1 p-2 px-3" title="Retake">
                                            Allow retake <i class="fas fa-redo"></i>
                                        </a>
                                    @elseif ($attempt['status'] === 'in_progress')
                                        <a href="{{ route('exams.endAttempt', [$exam->exam_code, $attempt['id']]) }}"
                                            class="btn flex-fill bg-gradient-danger m-1 p-2 px-3" title="Info">
                                            End Attempt
                                        </a>
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
        <x-pagination :paginator="$attempts" />
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
        });
    </script>
@endsection
@push('dashboard')
