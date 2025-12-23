@extends('layouts.user_type.auth')

@section('content')
<div>
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h6 class="mb-0">Buat Jadwal Ujian</h6>
        </div>
        <div class="card-body pt-4 p-3">

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('exams.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Informasi Jadwal Ujian Baru -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="title">Judul Ujian</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="exam_date">Tanggal</label>
                        <input type="date" name="exam_date" class="form-control" required
                            min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="duration">Durasi (menit)</label>
                        <input type="number" name="duration" class="form-control" required min="1">
                    </div>
                    {{-- <div class=" col-md-3 mb-3">
                        <label for="room">Ruangan</label>
                        <input type="text" name="room" class="form-control">
                    </div> --}}
                </div>

                <!-- Pilih Course -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <input type="text" class="form-control"
                            value="{{ $activeSemester->semester_name }} {{ $activeSemester->academicYear->year_name }}"
                            readonly>
                        <input type="hidden" name="semester_id" value="{{ $activeSemester->id }}">
                    </div>
                    <div class=" col-md-4 mb-3">
                        <label for="course_id" class="form-label">Pilih Course</label>
                        <select name="course_id" id="course_id" class="form-select" required>
                            <option value="">-- Pilih Course --</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="password">Password</label>
                        <input type="text" name="password" class="form-control" required>
                    </div>
                </div>
                <!-- Upload file Excel -->
                <div class="form-group mb-3">
                    <label for="file">Upload Soal</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv">
                </div>
                <a href="{{ asset('templates/template_soal.xlsx') }}" class="btn bg-gradient-info btn-sm" download>
                    <i class="fas fa-download me-1"></i>Template Soal
                </a>
                <button type="submit" class="btn btn-sm bg-gradient-success">Simpan Ujian</button>
            </form>
        </div>
    </div>
</div>
@endsection