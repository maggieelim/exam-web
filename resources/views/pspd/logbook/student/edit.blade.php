@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">
    <div class="card">
        <div class="card-body px-4">
            <form method="POST" action="{{ route('student-logbook.update', [$logbook->id]) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <h5>Edit Logbook</h5>

                <input type="hidden" name="rotation" value="{{ $rotation->id }}">

                <div class="row g-3">
                    {{-- Tanggal --}}
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" required
                            value="{{ old('date', $logbook->date->format('Y-m-d')) }}"
                            min="{{ $rotation->start_date->format('Y-m-d') }}"
                            max="{{ $rotation->end_date->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Waktu Mulai</label>
                        <input type="time" name="start_time" class="form-control" required
                            value="{{ old('start_time', $logbook->start_time?->format('H:i')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Waktu Selesai</label>
                        <input type="time" name="end_time" class="form-control" required
                            value="{{ old('end_time', $logbook->end_time?->format('H:i')) }}">
                    </div>
                    {{-- Jenis Kegiatan --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kegiatan</label>
                        <select class="form-select" name="activity" required>
                            <option value="">Pilih Kegiatan</option>
                            @foreach ($activity as $act)
                            <option value="{{ $act->id }}" {{ $logbook->activity_koas_id == $act->id ? 'selected' : ''
                                }}>
                                {{ $act->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Dosen/Dokter Pembimbing</label>
                        <select class="form-select" name="lecturer" required>
                            <option value="">Pilih Dosen</option>
                            @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->lecturer->id }}" {{ old('lecturer_id', $logbook->lecturer_id ??
                                '') == $lecturer->lecturer->id ? 'selected' : '' }}>
                                {{ $lecturer->lecturer->user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi Kegiatan</label>
                        <textarea name="desc" class="form-control" rows="1"
                            required>{{ old('desc', $logbook->description) }}</textarea>
                    </div>

                    {{-- Upload Bukti --}}
                    <div class="col-md-12">
                        <label class="form-label">Upload Bukti (Opsional)</label>
                        <input type="file" name="proof" class="form-control">
                        @if ($logbook->proof)
                        <small class="text-muted">
                            File sebelumnya: {{ $logbook->proof }}
                        </small>
                        @endif
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-sm bg-gradient-primary">
                        Update Logbook
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary ms-2">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection