@extends('layouts.user_type.auth')

@section('content')
{{-- Informasi Umum --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5>Informasi Kepaniteraan</h5>
        <div class="row gy-3">
            <div class="col-md-3">
                <small class="text-muted fw-bold">Rumah Sakit</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->hospitalRotation->hospital->name }}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted fw-bold">Stase</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->hospitalRotation->clinicalRotation->name }}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted fw-bold">Semester</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->semester->semester_name }}
                    {{ $logbook->studentKoas->semester->academicYear->year_name }}
                </div>
            </div>

            <div class="col-md-3">
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
        <div class="d-flex align-items-center justify-content-between w-100">
            <h5 class="mb-0">Detail Logbook</h5>

            <form action="{{ route('logbook.update', ['status' => $status, 'logbook' => $logbook->id]) }}" method="POST"
                class="d-inline">
                @csrf
                @method('PUT')

                <button @if ($logbook->status === 'rejected') hidden @endif
                    type="submit"
                    name="action"
                    value="rejected"
                    class="btn btn-danger px-3 py-2"
                    title="Decline"
                    >
                    <i class="fa fa-times"></i>
                    <span class="d-none d-sm-inline ms-1">Decline</span>
                </button>

                <button @if ($logbook->status === 'approved') hidden @endif
                    type="submit"
                    name="action"
                    value="approved"
                    class="btn btn-success px-3 py-2"
                    title="Approve"
                    >
                    <i class="fa fa-check"></i>
                    <span class="d-none d-sm-inline ms-1">Approve</span>
                </button>
            </form>
        </div>
        <div class="row gy-3">
            <div class="col-md-3">
                <small class="text-muted fw-bold">Student</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->student->user->name }}
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted fw-bold">NIM</small>
                <div class="fw-semibold">
                    {{ $logbook->studentKoas->student->nim }}
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted fw-bold">Jenis Kegiatan</small>
                <div class="fw-semibold">
                    {{ $logbook->activityKoas->name }}
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted fw-bold">Waktu</small>
                <div class="fw-semibold">
                    {{ $logbook->date->format('d M Y') }},
                    {{ optional($logbook->start_time)->format('H.i') ?? '' }} -
                    {{ optional($logbook->end_time)->format('H.i') ?? '' }}
                </div>
            </div>

            <div class="col-md-12">
                <small class="text-muted fw-bold">Keterangan</small>
                <div class="fw-semibold">
                    {{ $logbook->description }}
                </div>
            </div>

            @if ($logbook->file_path)
            <div class="col-md-6">
                <a href="{{ asset('storage/' . $logbook->file_path) }}" target="_blank"
                    class="btn btn-sm btn-outline-primary">
                    Lihat Bukti
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection