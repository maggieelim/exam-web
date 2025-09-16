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
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Mahasiswa</h6>
        <form method="GET" action="{{ route('admin.courses.show', $course->slug) }}" class="d-flex">
          <input type="text" name="nim" class="form-control form-control-sm me-2" placeholder="Filter NIM" value="{{ request('nim') }}">
          <input type="text" name="name" class="form-control form-control-sm me-2" placeholder="Filter Nama" value="{{ request('name') }}">
          <button type="submit" class="btn btn-sm btn-primary">Filter</button>
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