@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 card mb-4">
    <div class="card-header d-flex flex-row justify-content-between">
      <div>
        <h5 class="mb-0">Exams List</h5>
      </div>
    </div>
  </div>
  @foreach($exams as $exam)
  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card h-auto">
      <div class="card-body p-3">
        <div class="row">
          <div class="d-flex flex-column h-100">
            <h5 class="font-weight-bolder">{{ $exam->title }}</h5>
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

            <p class="mb-4">
              <i class="fas fa-map-marker-alt me-2"></i>
              Room: {{ $exam->room }}
            </p>

            <!-- Button Section berdasarkan status exam -->
            <div class="mt-auto">
              @if($exam->has_completed)
              <a class="btn btn-sm btn-success w-100 disabled">
                Completed
              </a>

              @elseif($exam->has_ongoing)
              <div class="text-center">
                <span class="badge bg-warning mb-2">Ongoing</span>
                <br>
                <a href="{{ route('student.exams.continue', $exam->exam_code) }}" class="btn btn-sm btn-warning">
                  <i class="fas fa-play me-1"></i> Lanjutkan Ujian
                </a>
              </div>

              @elseif($exam->show_start_button)
              <button class="btn btn-sm btn-primary w-100"
                data-bs-toggle="modal"
                data-bs-target="#examPasswordModal-{{ $exam->exam_code }}">
                <i class="fas fa-play me-1"></i> Start Exam
              </button>

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
                          <input type="text" name="password" class="form-control"
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
              @elseif($exam->exam_ended)
              <div class="text-center">
                <a class="btn btn-sm btn-danger w-100 disabled">
                  Exam has ended
                </a>
              </div>
              @else
              <div class="text-center">
                <small class="text-muted">
                  Available soon </small>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection

@push('dashboard')
@endpush