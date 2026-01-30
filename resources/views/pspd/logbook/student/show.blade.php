@extends('layouts.user_type.auth')

@section('content')

{{-- Informasi Umum --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5>Informasi Kepaniteraan</h5>
        <div class="row gy-3">
            <div class="col-md-6">
                <small class="text-muted fw-bold">Rumah Sakit</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->hospitalRotation->hospital->name }}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-muted fw-bold">Stase</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->hospitalRotation->clinicalRotation->name }}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-muted fw-bold">Semester</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->semester->semester_name }}
                    {{ $logbook->studentKoas->semester->academicYear->year_name }}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-muted fw-bold">Periode</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->start_date->format('d M Y') }} –
                    {{ $logbook->studentKoas->end_date->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail Logbook --}}
<div class="card shadow-sm">
    <div class="card-body">
        <h5>Detail Logbook</h5>
        <div class="row gy-3">
            <div class="col-md-4">
                <small class="text-muted fw-bold">Tanggal</small>
                <div class="fw-semibold">
                    {{ $logbook->date->format('d M Y') }}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted fw-bold">Jenis Kegiatan</small>
                <div class="fw-semibold">
                    {{ $logbook->activityKoas->name }}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted fw-bold">Dosen/Dokter Pembimbing</small>
                <div class="fw-semibold">
                    {{ $logbook->lecturer->user->name }} {{ $logbook->lecturer->gelar }}
                </div>
            </div>

            <div class="col-md-12">
                <small class="text-muted fw-bold">Keterangan</small>
                <div class="fw-semibold">
                    {{ $logbook->description }}
                </div>
            </div>

            @if ($logbook->file_path)
            <a href="{{ asset('storage/' . $logbook->file_path) }}" target="_blank"
                class="btn btn-sm btn-outline-primary">
                Lihat Bukti
            </a>
            @endif
        </div>
    </div>
</div>

@endsection