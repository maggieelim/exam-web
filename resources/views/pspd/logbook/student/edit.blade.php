@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Create Logbook Baru</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">

            <form method="POST" action="{{ route('student-logbook.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" required
                            min="{{ \Carbon\Carbon::parse($rotation->start_date)->format('Y-m-d') }}"
                            max="{{ \Carbon\Carbon::parse($rotation->end_date)->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Jenis Kegiatan</label>
                        <select class="form-select" name="activity">
                            <option>Pilih Kegiatan</option>
                            @foreach ($activity as $act)
                            <option value="{{ $act->id }}">{{ $act->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-md-12">
                        <label>Deskripsi Kegiatan</label>
                        <input type="text" name="desc" class="form-control" required>
                        <input hidden type="text" name="rotation" class="form-control" required
                            value="{{ $rotation->id }}">
                    </div>
                    <div class="mb-3 col-md-12">
                        <label>Dosen/Dokter Pembimbing</label>
                        <input type="text" name="lecturer" class="form-control" list="lecturer_list"
                            placeholder="Pilih Dosen" required>

                        <datalist id="lecturer_list">
                            @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}">
                                {{ $lecturer->user->name }}
                            </option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3 col-md-12">
                        <label>Upload Bukti</label>
                        <input type="file" name="proof" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <button type="submit" class="btn bg-gradient-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection