@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4 p-3">
      <h5 class="mb-3">{{ $course->name }}</h5>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Kode Blok:</strong> {{ $course->kode_blok }}</p>
        </div>
        <div class="col-md-6 d-flex">
          <strong class="me-3">Dosen:</strong>
          <ul class="mb-0 ps-3">
            @foreach($course->lecturers as $lecturer)
            <li>{{ $lecturer->name }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>

    <!-- Card Daftar Mahasiswa -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-end">
        <button class="btn btn-sm btn-outline-secondary" type="button"
          data-bs-toggle="collapse" data-bs-target="#filterCollapse"
          aria-expanded="false" aria-controls="filterCollapse">
          <i class="fas fa-filter"></i> Filter
        </button>
      </div>

      <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('admin.courses.show', $course->slug) }}">
          <div class="mx-3 my-2 py-2">
            <div class="row g-2">
              <!-- Input Blok -->
              <div class="col-md-6">
                <label for="blok" class="form-label mb-1">NIM</label>
                <input type="text" name="nim" class="form-control form-control-sm me-2" placeholder="Filter NIM" value="{{ request('nim') }}">
              </div>

              <!-- Input Dosen -->
              <div class="col-md-6">
                <label for="dosen" class="form-label mb-1">Nama</label>
                <input type="text" name="name" class="form-control form-control-sm me-2" placeholder="Filter Nama" value="{{ request('name') }}">
              </div>

              <!-- Buttons -->
              <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('admin.courses.show', $course->slug) }}" class="btn btn-light btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
              </div>
            </div>
          </div>
        </form>
      </div>



      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">NIM</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Nama</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($course->students as $student)
              @if( (!request('nim') || str_contains($student->student->nim, request('nim'))) &&
              (!request('name') || str_contains(strtolower($student->name), strtolower(request('name')))) )
              <tr>
                <td class="align-middle text-center">{{ $student->student->nim }}</td>
                <td class="align-middle text-center">{{ $student->name }}</td>
                <td class="align-middle text-center">
                  <a href="{{ route('admin.courses.edit', $course->slug) }}"
                    class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <a href="{{ route('admin.courses.show', $course->slug) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                </td>
              </tr>
              @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('dashboard')