@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex flex-row justify-content-between mb-0 pb-0">
        <div>
          <h5 class="mb-0">Exams List</h5>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> Filter
          </button>
          <a href="{{ route('exams.create') }}"
            class="btn bg-gradient-primary btn-sm"
            type="button">
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
              <div class="col-md-6">
                <label for="title" class="form-label mb-1">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Cari Judul Ujian"
                  value="{{ request('title') }}">
              </div>
              <div class="col-md-6">
                <label for="blok" class="form-label mb-1">Blok</label>
                <select name="course_id" class="form-control">
                  <option value="">-- Pilih Course --</option>
                  @foreach($courses as $course)
                  <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                    {{ $course->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('exams.index') }}" class="btn btn-light btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
              </div>
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
                  <a href="{{ route('exams.index', $status) }}?{{ http_build_query(array_merge(request()->except('page'), [
            'sort' => 'title',
            'dir'  => ($sort === 'title' && $dir === 'asc') ? 'desc' : 'asc'
        ])) }}"
                    class="text-dark text-decoration-none">
                    Title
                    @if($sort === 'title')
                    <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
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
              @foreach($exams as $exam)
              <tr>
                <td class="align-middle px-3">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->title }} <br>
                    {{ $exam->course->name }} <br>
                  </span>
                  <span class="text-sm">
                    Modified at: {{ \Carbon\Carbon::parse($exam->updated_at)->format('j/n/y H.i') }} by {{ $exam->updater->name }}
                  </span>
                </td>
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->questions_count > 0 ? $exam->questions_count . ' Questions' : 'No Questions Yet' }}
                  </span>
                </td>
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->duration . ' Minutes' }}
                  </span>
                </td>
                <td class="align-middle text-center">
                  @if($exam->status === 'upcoming')
                  <button type="button"
                    class="btn bg-gradient-success m-1 p-2 px-3 start-exam-btn"
                    data-exam-id="{{ $exam->id }}"
                    data-exam-title="{{ $exam->title }}"
                    data-action-url="{{ route('exams.start', $exam->id) }}">
                    Start
                  </button>
                  <a href="{{ route('exams.edit', [$status, $exam->exam_code]) }}"
                    class="btn bg-gradient-primary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-edit "></i> </a>
                  <a href="{{ route('exams.show.upcoming', [$exam->exam_code]) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                  @elseif($exam->status === 'ongoing')
                  <button type="button"
                    class="btn bg-gradient-danger m-1 p-2 px-3 end-exam-btn"
                    data-exam-id="{{ $exam->id }}"
                    data-exam-title="{{ $exam->title }}"
                    data-action-url="{{ route('exams.end', $exam->id) }}">
                    End
                  </button>
                  <a href="{{ route('exams.ongoing', [$exam->exam_code]) }}"
                    class="btn bg-gradient-primary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-users me-1"></i> </a>
                  <a href="{{ route('exams.show.ongoing', [$exam->exam_code]) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                  @else
                  <span class="badge bg-secondary">Ended</span>
                  <a href="{{ route('exams.show.previous', [$exam->exam_code]) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                  @endif
                </td>
              </tr>
              @endforeach
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