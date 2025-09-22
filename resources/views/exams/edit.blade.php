@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
  <div class="card">
    <div class="card-header pb-0 px-3">
      <h6 class="mb-0">Edit Exam</h6>
    </div>
    <div class="card-body pt-4 p-3">
      <!-- Form Update Exam -->
      <form action="{{ route('exams.update', $exam->exam_code) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Informasi Jadwal Ujian -->
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="title">Judul Ujian</label>
            <input type="text" name="title" class="form-control" required value="{{ $exam->title }}">
          </div>

          <div class="col-md-2 mb-3">
            <label for="exam_date">Tanggal</label>
            <input type="date" name="exam_date" class="form-control" required value="{{ old('exam_date', isset($exam->exam_date) ? \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') : '') }}">
          </div>

          <div class="col-md-2 mb-3">
            <label for="clock">Jam</label>
            <input type="time" name="clock" class="form-control" required value="{{ old('clock', isset($exam->exam_date) ? \Carbon\Carbon::parse($exam->exam_date)->format('H:i') : '') }}">
          </div>

          <div class="col-md-2 mb-3">
            <label for="duration">Durasi (menit)</label>
            <input type="number" name="duration" class="form-control" required min="1" value="{{ $exam->duration }}">
          </div>

          <div class="col-md-2 mb-3">
            <label for="room">Ruangan</label>
            <input type="text" name="room" class="form-control" value="{{ $exam->room }}">
          </div>
        </div>

        <!-- Pilih Course -->
        <div class="mb-3">
          <label for="course_id" class="form-label">Pilih Course</label>
          <select name="course_id" id="course_id" class="form-select" required>
            <option value="">-- Pilih Course --</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ $exam->course_id == $course->id ? 'selected' : '' }}>
              {{ $course->name }}
            </option>
            @endforeach
          </select>
        </div>

        <!-- Tombol Update -->
        <div class="mb-3">
          <button type="submit" class="btn btn-sm btn-primary">Update Exams</button>
        </div>
      </form>

      <!-- Form Delete Exam (terpisah) -->
      <form action="{{ route('exams.destroy', $exam->exam_code) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus exam ini?')" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">Delete Exams</button>
      </form>

    </div>
  </div>
</div>
@endsection