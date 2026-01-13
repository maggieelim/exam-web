@extends('layouts.user_type.auth')

@section('content')
<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
      <h5 class="mb-0">Exams List</h5>
      @if ($semesterId)
      @php
      $selectedSemester = $semesters->firstWhere('id', $semesterId);
      @endphp
      <x-semester-badge :semester="$selectedSemester" :activeSemester="$activeSemester" />
      @endif
    </div>
    <button class="btn py-2 px-3 btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
      data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
      <i class="fas fa-filter"></i> Filter
    </button>
  </div>

  <div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('student.studentExams.index', $status) }}">
      <div class="mx-3 mb-2 pb-2">
        <div class="row g-2">
          <input type="hidden" name="status" value="{{ $status }}">
          <div class="col-md-4">
            <label for="title" class="form-label mb-1">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Cari Judul Ujian"
              value="{{ request('title') }}">
          </div>
          <div class="col-md-4">
            <label for="blok" class="form-label mb-1">Blok</label>
            <select name="course_id" class="form-control">
              <option value="">-- ALL --</option>
              @foreach($courses as $course)
              <option value="{{ $course->id }}" {{ request('course_id')==$course->id ? 'selected' : '' }}>
                {{ $course->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="semester" class="form-label mb-1">Semester</label>
            <select name="semester_id" class="form-control">
              @foreach($semesters as $semester)
              <option value="{{ $semester->id }}" {{ ($semesterId==$semester->id) ? 'selected' : '' }}>
                {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                @if($activeSemester && $semester->id == $activeSemester->id)
                (Aktif)
                @endif
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('student.studentExams.index', $status) }}" class="btn btn-light btn-sm">Reset</a>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row g-3">
  @foreach($exams as $exam)
  <div class="col-12 col-md-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column p-3 pb-0">
        <h5 class="fw-bolder">{{ $exam->title }}</h5>
        <p class="mb-1 text-secondary">
          {{ $exam->course->name }}
        </p>

        <p class="mb-1">
          <i class="fas fa-calendar me-2"></i>
          {{ \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y') }}
        </p>

        <p class="mb-1">
          <i class="fas fa-hourglass-half me-2"></i>
          Duration: {{ $exam->duration }} minutes
        </p>

        <!-- Button Section berdasarkan status exam -->
        <div class="mt-auto">
          @if($exam->has_completed)
          @if($exam->is_published)
          <a href="{{ route('student.results.show', $exam->exam_code) }}" class="btn bg-gradient-success py-2 w-100"
            title="Results">
            <i class="fas fa-clipboard-check me-2"></i> See Results
          </a>
          @else
          <div class="btn bg-gradient-secondary opacity-75 w-100 py-2" disabled>
            <i class="fas fa-hourglass-half me-2"></i> Waiting for Grading
          </div>
          @endif
          @elseif( optional($exam->attempts->first())->status ==='in_progress')
          <button class="btn btn-sm btn-warning w-100" data-bs-toggle="modal"
            data-bs-target="#examPasswordModal-{{ $exam->exam_code }}">
            <i class="fas fa-play me-1"></i> Lanjutkan Ujian
          </button>

          @elseif($exam->show_start_button)
          <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal"
            data-bs-target="#examPasswordModal-{{ $exam->exam_code }}">
            <i class="fas fa-play me-1"></i> Start Exam
          </button>

          @elseif($exam->status ==='upcoming')
          <div class="text-center">
            <a class="btn btn-sm bg-gradient-secondary w-100 disabled">
              Available soon
            </a>
          </div>
          @else
          <div class="text-center">
            <a class="btn btn-sm bg-gradient-danger w-100 disabled">
              Exam has ended
            </a>
          </div>
          @endif
          <!-- Modal -->
          <div class="modal fade" id="examPasswordModal-{{ $exam->exam_code }}" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" action="{{ route('student.exams.start', $exam->exam_code) }}">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Enter Exam Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label for="password-{{ $exam->exam_code }}" class="form-label">Exam Password</label>
                      <input type="text" name="password" class="form-control" autocomplete="off"
                        id="password-{{ $exam->exam_code }}" placeholder="Enter exam password" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Exam</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div class="d-flex justify-content-center mt-3">
  <x-pagination :paginator="$exams" />
</div>
@endsection

@push('dashboard')
@endpush