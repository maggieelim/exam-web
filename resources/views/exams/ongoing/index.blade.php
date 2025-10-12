@extends('layouts.user_type.auth')

@section('content')

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="fas fa-chart-bar me-2"></i>{{ $exam['title'] ?? 'Exam Title' }} - Ongoing Participants
    </h5>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">
        <i class="fas fa-sync-alt me-1"></i> Refresh
      </button>
      <a href="{{ route('exams.index', $status='ongoing') }}" class="btn btn-sm btn-secondary">Back</a>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-4 col-6">
        <div class="text-center">
          <h3 class="text-primary">{{ $stats['total_participants'] ?? 0 }}</h3>
          <p class="text-muted mb-0">Total Participants</p>
        </div>
      </div>
      <div class="col-md-4 col-6">
        <div class="text-center">
          <h3 class="text-primary">{{ $stats['active_participants'] ?? 0 }}</h3>
          <p class="text-muted mb-0">Active Participants</p>
        </div>
      </div>
      <div class="col-md-4 col-6">
        <div class="text-center">
          <h3 class="text-info">{{ $stats['completed_participants'] ?? 0 }}</h3>
          <p class="text-muted mb-0">Completed Participants</p>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-end align-items-center mb-0 pb-0">
    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
      <i class="fas fa-filter"></i> Filter
    </button>
  </div>
  <!-- Collapse Form -->
  <div class="collapse" id="filterCollapse">
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
            @foreach($availableStatuses as $key => $label)
            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
              {{ $label }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
          <a href="{{ route('exams.ongoing', $exam->exam_code)  }}" class="btn btn-light btn-sm">Reset</a>
          <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        </div>
      </div>
    </form>
  </div>
  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive pb-5">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ route('exams.ongoing', $exam->exam_code) }}?{{ http_build_query(array_merge(request()->except('page'), [
            'sort' => 'nim',
            'dir'  => ($sort === 'nim' && $dir === 'asc') ? 'desc' : 'asc'
        ])) }}"
                class="text-dark text-decoration-none">
                NIM
                @if($sort === 'nim')
                <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ route('exams.ongoing', $exam->exam_code) }}?{{ http_build_query(array_merge(request()->except('page'), [
            'sort' => 'name',
            'dir'  => ($sort === 'name' && $dir === 'asc') ? 'desc' : 'asc'
        ])) }}"
                class="text-dark text-decoration-none">
                Name
                @if($sort === 'name')
                <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              Answered
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              Status
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              Action
            </th>
          </tr>
        </thead>

        <tbody>
          @foreach($attempts as $attempt)
          <tr>
            <td class="align-middle text-center">
              <span class="text-sm font-weight-bold">
                {{ $attempt['nim']}}
              </span>
            </td>
            <td class="align-middle text-center">
              <span class="text-sm font-weight-bold">
                {{ $attempt['student_name']}}
              </span>
            </td>
            <td class="align-middle text-center">
              <span class="text-sm font-weight-bold">
                {{ $attempt['answered_count']}}/ {{ $attempt['total_questions']}}
              </span>
            </td>

            <td class="align-middle text-center">
              <span class="badge {{ $attempt['status_badge']['class'] }}">
                {{ $attempt['status_badge']['text'] }}
              </span>
            </td>
            <td class="align-middle text-center">
              <a href="{{ route('exams.retake', [$exam->exam_code, $attempt['id'] ]) }}"
                class="btn bg-gradient-warning m-1 p-2 px-3" title="Info">
                Allow retake <i class="fas fa-redo"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
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