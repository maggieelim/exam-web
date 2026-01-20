@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4 d-flex flex-column gap-3">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Penugasan Dosen Kepaniteraan</h5>
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

    <div class="card">
        <form method="POST" action="{{ route('lecturer-koas.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div>
                    <input type="hidden" name="rotation" value="{{ $rotation->id }}">
                    <input type="hidden" name="semester" value="{{ $rotation->semester_id }}">
                    <input type="hidden" name="start_date" value="{{ $rotation->start_date }}">
                    <input type="hidden" name="end_date" value="{{ $rotation->end_date }}">

                    <label for="excel" class="form-label">Upload File Excel</label>
                    <input type="file" name="excel" id="excel" class="form-control" accept=".xlsx,.xls,.csv">

                    <div class="mt-2">
                        <a href="{{ asset('templates/import_mahasiswa_peserta_koas.xlsx') }}"
                            class="btn btn-info btn-sm" download>
                            <i class="fas fa-file-excel me-1"></i>Download Template
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div class="d-flex align-items-center">
                    <hr class="flex-grow-1">
                    <span class="mx-3 text-muted">atau</span>
                    <hr class="flex-grow-1">
                </div>

                <div class="mb-3">
                    <label for="nim" class="form-label">Masukkan NIM Manual</label>
                    <textarea class="form-control" name="nim" id="nim" rows="5"
                        placeholder="Pisahkan dengan Enter. Contoh:&#10;134561&#10;156782&#10;179003"></textarea>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
    @endsection