@extends('layouts.user_type.auth')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="text-uppercase">Penjadwalan-Kelas</h5>
            <div class="row">
                <div class = "col-md-4 col-12">
                    <p><strong>Tahun Ajaran:</strong> {{ $semester->academicYear->year_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Semester:</strong> {{ $semester->semester_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Blok:</strong> {{ $course->name }}</p>
                </div>
            </div>
        </div>
        <div class="card-body mt-0 pt-0">
            <form action="{{ route('admin.course.store', $course->id) }}" method="POST">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="year_level" value="{{ $course->year_level ?? 1 }}">
                <input type="hidden" name="is_update" value="{{ $existingSchedule ? '1' : '0' }}">
                <div class="row">
                    @foreach ($activities as $a)
                        @php
                            $value = $existingCounts[$a->id] ?? 0;
                        @endphp
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="mb-0 fw-semibold">
                                    {{ $a->activity_name }}
                                    ({{ $a->code }})
                                </label>
                                <input type="number" name="activities[{{ $a->id }}]"
                                    class="form-control text-center ms-3" style="width: 60%;" min="0"
                                    value="{{ $value }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    {{ $existingSchedule ? 'Perbarui Jadwal' : 'Simpan Jadwal' }}
                </button>
            </form>
        </div>
    </div>
@endsection
