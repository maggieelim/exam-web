@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Buat Tahun Akademik Baru</h5>
      </div>
      <div class="card-body px-4 pt-3 pb-3">
        <form method="POST" action="{{ route('admin.semester.store') }}">
          @csrf

          {{-- Informasi Tahun Akademik --}}
          <h6 class="fw-bold mt-2">Informasi Tahun Akademik</h6>
          <div class="row mb-4">
            <div class="col-md-4">
              <label for="year_name" class="form-label">Tahun Akademik</label>
              <select name="year_name" id="year_name" class="form-select" required>
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach ($academicYears as $year)
                <option value="{{ $year }}">
                  {{ $year }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4 mb-3">
              <label for="start_date" class="form-label fw-bold">Tanggal Mulai Tahun Akademik</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                required value="{{ old('start_date') }}">
            </div>

            <div class="col-md-4 mb-3">
              <label for="end_date" class="form-label fw-bold">Tanggal Selesai Tahun Akademik</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                required value="{{ old('end_date') }}">
            </div>
          </div>

          {{-- Semester Ganjil --}}
          <h6 class="fw-bold  mt-2">Semester Ganjil</h6>
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="odd_start" class="form-label">Tanggal Mulai Ganjil</label>
              <input type="date" name="odd_start" id="odd_start" class="form-control"
                required value="{{ old('odd_start') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label for="odd_end" class="form-label">Tanggal Selesai Ganjil</label>
              <input type="date" name="odd_end" id="odd_end" class="form-control"
                required value="{{ old('odd_end') }}">
            </div>
          </div>

          {{-- Semester Genap --}}
          <h6 class="fw-bold  mt-2">Semester Genap</h6>
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="even_start" class="form-label">Tanggal Mulai Genap</label>
              <input type="date" name="even_start" id="even_start" class="form-control"
                required value="{{ old('even_start') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label for="even_end" class="form-label">Tanggal Selesai Genap</label>
              <input type="date" name="even_end" id="even_end" class="form-control"
                required value="{{ old('even_end') }}">
            </div>
          </div>

          {{-- Tombol Simpan --}}
          <div class="row">
            <div class="col-md-2">
              <button type="submit" class="btn bg-gradient-primary w-100">Simpan</button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection