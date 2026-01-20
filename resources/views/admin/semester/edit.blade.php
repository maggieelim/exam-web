@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0 d-flex justify-content-between">
        <h5 class="mb-0 text-uppercase">Edit Semester {{ $semester->semester_name }} {{
          $semester->academicYear->year_name }}</h5>
        <a href="{{ route(session('context') . '.admin.semester.index') }}" class="btn btn-sm btn-secondary">Back</a>
      </div>
      <div class="card-body px-4 pt-3 pb-3">
        <form method="POST" action="{{ route(session('context') . '.admin.semester.update', $semester->id) }}">
          @csrf
          @method('PUT')
          {{-- Informasi Tahun Akademik --}}
          <h6 class="fw-bold mt-2">Informasi Tahun Akademik</h6>
          <div class="row mb-4">
            <div class="col-md-4">
              <label for="year_name" class="form-label">Tahun Akademik</label>
              <select name="year_name" id="year_name" class="form-select" required>
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach ($academicYears as $year)
                <option value="{{ $year }}" {{ old('year_name', $semester->academicYear->year_name ?? '') == $year ?
                  'selected' : '' }}>
                  {{ $year }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4 mb-3">
              <label for="start_date" class="form-label">Tanggal Mulai Tahun Akademik</label>
              <input type="date" name="start_date" id="start_date" class="form-control" required
                value="{{ old('start_date', $semester->academicYear->start_date ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
              <label for="end_date" class="form-label">Tanggal Berakhir Tahun Akademik</label>
              <input type="date" name="end_date" id="end_date" class="form-control" required
                value="{{ old('end_date', $semester->academicYear->end_date ?? '') }}">
            </div>
          </div>

          {{-- Semester Genap --}}
          <h6 class="fw-bold  mt-2">Semester {{ $semester->semester_name }}</h6>
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="semester_start" class="form-label">Tanggal Mulai Semester</label>
              <input type="date" name="semester_start" id="semester_start" class="form-control" required
                value="{{ old('semester_start', $semester->start_date ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label for="semester_end" class="form-label">Tanggal Berakhir Semester</label>
              <input type="date" name="semester_end" id="semester_end" class="form-control" required
                value="{{ old('semester_end', $semester->end_date ?? '') }}">
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

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const yearSelect = document.getElementById("year_name");
    const startDate = document.getElementById("start_date");
    const endDate = document.getElementById("end_date");
    const semesterStart = document.getElementById("semester_start");
    const semesterEnd = document.getElementById("semester_end");

    function setDateRange(yearValue) {
      if (yearValue && yearValue.includes("/")) {
        const [startYear, endYear] = yearValue.split("/").map(y => parseInt(y));
        const minDate = `${startYear}-01-01`;
        const maxDate = `${endYear}-12-31`;

        [startDate, endDate, semesterStart, semesterEnd].forEach(input => {
          input.min = minDate;
          input.max = maxDate;
          if (input.value && (input.value < minDate || input.value > maxDate)) {
            input.value = "";
          }
        });
      } else {
        [startDate, endDate, semesterStart, semesterEnd].forEach(input => {
          input.removeAttribute("min");
          input.removeAttribute("max");
        });
      }
    }

    // Set default range ketika halaman pertama kali dimuat
    setDateRange(yearSelect.value);

    // Update ketika tahun akademik diganti
    yearSelect.addEventListener("change", function() {
      setDateRange(this.value);
    });
  });
</script>

@endsection