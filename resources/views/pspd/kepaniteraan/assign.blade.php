@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4 d-flex flex-column gap-3">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Penugasan Mahasiswa dan Dosen Kepaniteraan</h5>
        </div>
        <div class="card-body row px-4 pt-2 pb-2">
            <div class="col-md-6">
                <p><strong>Rumah Sakit:</strong> {{ $rotation->hospital->name }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Stase:</strong> {{ $rotation->clinicalRotation->name }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Semester:</strong> {{ $rotation->semester->semester_name }} {{
                    $rotation->semester->academicYear->year_name }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Periode:</strong> {{ $rotation->start_date->format('d M Y') }} - {{
                    $rotation->end_date->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <form method="POST" action="{{ route('mahasiswa-koas.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <h6 class="mb-2">Penugasan Mahasiswa</h6>

                        <!-- Hidden fields -->
                        <input type="hidden" name="rotation" value="{{ $rotation->id }}">
                        <input type="hidden" name="semester" value="{{ $rotation->semester_id }}">
                        <input type="hidden" name="start_date" value="{{ $rotation->start_date }}">
                        <input type="hidden" name="end_date" value="{{ $rotation->end_date }}">

                        <!-- Upload Excel -->
                        <div class="mb-3">
                            <label for="excel" class="form-label">Upload File Excel</label>

                            <div class="d-flex gap-3 align-items-center">
                                <input type="file" name="excel" id="excel" class="form-control flex-grow-1"
                                    accept=".xlsx,.xls,.csv">

                                <a href="{{ asset('templates/import_mahasiswa_peserta_koas.xlsx') }}"
                                    class="btn btn-info text-nowrap m-auto" download>
                                    <i class="fas fa-file-excel me-1"></i> Template
                                </a>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="d-flex align-items-center">
                            <hr class="flex-grow-1">
                            <span class="mx-3 text-muted">atau</span>
                            <hr class="flex-grow-1">
                        </div>

                        <!-- Manual NIM -->
                        <div class="mb-3">
                            <label for="nim" class="form-label">Masukkan NIM Manual</label>
                            <textarea class="form-control" name="nim" id="nim" rows="5"
                                placeholder="Pisahkan dengan Enter. Contoh:&#10;134561&#10;156782&#10;179003"></textarea>
                        </div>

                        <button type="submit" class="btn bg-gradient-primary mb-0">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <form method="POST" action="{{ route('lecturer-koas.store') }}">
                    @csrf

                    <div class="card-body">
                        <h6 class="mb-2">Penugasan Dosen</h6>

                        <!-- Hidden fields -->
                        <input type="hidden" name="rotation" value="{{ $rotation->id }}">
                        <input type="hidden" name="semester" value="{{ $rotation->semester_id }}">
                        <input type="hidden" name="start_date" value="{{ $rotation->start_date }}">
                        <input type="hidden" name="end_date" value="{{ $rotation->end_date }}">

                        <!-- Lecturer Select -->
                        <div class="mb-3">
                            <label class="form-label">Dosen Koas</label>
                            <select name="lecturers[]" class="form-select choices" multiple required>
                                @foreach ($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}" {{ in_array($lecturer->id, $selectedLecturers ?? [])
                                    ? 'selected' : '' }}>
                                    {{ ucfirst($lecturer->user->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn bg-gradient-primary mb-0">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    @endsection