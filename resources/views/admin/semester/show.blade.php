@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-uppercase">
                    Detail Semester {{ $semester->semester_name }} {{ $semester->academicYear->year_name }}
                </h5>
                <div>
                    <a href="{{ route(session('context').'.admin.semester.index') }}"
                        class="btn btn-sm btn-secondary">Back</a>
                    <a href="{{ route(session('context').'.admin.semester.edit', $semester->id) }}"
                        class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route(session('context') . '.admin.semester.destroy', $semester->id) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus semester ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body px-4 pt-3 pb-3">
                {{-- Informasi Tahun Akademik --}}
                <h6 class="fw-bold mt-2">Informasi Tahun Akademik</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tahun Akademik</label>
                        <input type="text" class="form-control" value="{{ $semester->academicYear->year_name }}"
                            readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Mulai Tahun Akademik</label>
                        <input type="date" class="form-control" value="{{ $semester->academicYear->start_date }}"
                            readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Berakhir Tahun Akademik</label>
                        <input type="date" class="form-control" value="{{ $semester->academicYear->end_date }}"
                            readonly>
                    </div>
                </div>

                {{-- Informasi Semester --}}
                <h6 class="fw-bold mt-2">Semester {{ $semester->semester_name }}</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Mulai Semester</label>
                        <input type="date" class="form-control" value="{{ $semester->start_date }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Berakhir Semester</label>
                        <input type="date" class="form-control" value="{{ $semester->end_date }}" readonly>
                    </div>
                </div>

                {{-- Informasi Tambahan --}}
                <h6 class="fw-bold mt-2">Informasi Tambahan</h6>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Dibuat</label>
                        <input type="text" class="form-control"
                            value="{{ $semester->created_at ? $semester->created_at->format('d M Y H:i') : '-' }}"
                            readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Terakhir Diperbarui</label>
                        <input type="text" class="form-control"
                            value="{{ $semester->updated_at ? $semester->updated_at->format('d M Y H:i') : '-' }}"
                            readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection