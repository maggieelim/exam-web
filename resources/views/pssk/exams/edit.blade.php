@extends('layouts.user_type.auth')

@section('content')
<div class="card mb-4">
  <div class="card-header pb-0 px-3">
    <h6 class="mb-0">Edit Exam</h6>
  </div>
  <div class="card-body pt-4 p-3">
    <!-- Form Update Exam -->
    <form action="{{ route('exams.update', [$status,$exam->exam_code]) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Informasi Jadwal Ujian -->
      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="title">Judul Ujian</label>
          <input type="text" name="title" class="form-control" required value="{{ $exam->title }}" {{
            $status==='ongoing' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-4 mb-3">
          <label for="exam_date">Tanggal</label>
          <input type="date" name="exam_date" class="form-control" required
            value="{{ old('exam_date', isset($exam->exam_date) ? \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') : '') }}"
            {{ $status==='ongoing' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-4 mb-3">
          <label for="duration">Durasi (menit)</label>
          <input type="number" name="duration" class="form-control" required min="1" value="{{ $exam->duration }}">
        </div>
      </div>

      <!-- Pilih Course -->
      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="semester_id" class="form-label">Semester</label>
          <input type="text" class="form-control"
            value="{{ $exam->semester->semester_name }} {{ $exam->semester->academicYear->year_name }}" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Course</label>

          {{-- TAMPILAN --}}
          <input type="text" class="form-control" value="{{ $exam->course->name ?? '' }}" readonly>

          {{-- VALUE UNTUK BACKEND --}}
          <input type="hidden" name="course_id" value="{{ $exam->course_id }}">
        </div>

        <div class="col-md-4 mb-3">
          <label for="password">Password</label>
          <input type="text" name="password" class="form-control" value="{{$exam->password}}" required>
        </div>
      </div>
      <!-- Tombol Update -->
      <div class="mb-3">
        <button type="submit" class="btn btn-sm btn-primary">Update
          Exams</button>
      </div>
    </form>
  </div>
</div>
@endsection