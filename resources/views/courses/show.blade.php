@extends('layouts.user_type.auth')

@section('content')

<div class="card mb-4 p-3">
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="mb-3">{{ $course->name }}</h5>
    <div class="d-flex gap-2">
      {{-- Tombol Download --}}
      <a href="{{ route('courses.download', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
        class="btn btn-sm btn-success">
        <i class="fas fa-download me-2"></i>
        Export Excel
      </a>
      @role('admin')
      {{-- Tombol Delete --}}
      <form action="{{ route('courses.destroy', $course->slug) }}" method="POST"
        onsubmit="return confirm('Yakin ingin menghapus course ini?')" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
          Delete Course
        </button>
      </form>
      @endrole
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <p><strong>Kode Blok:</strong> {{ $course->kode_blok }}</p>
    </div>
    <div class="col-md-6 d-flex gap-2">
      <p><strong>Lecturer:</strong></p>
      <ul class="mb-0 ps-3">
        @foreach($lecturers as $lecturer)
        <li>{{ $lecturer->lecturer->user->name }}</li>
        @endforeach
      </ul>
    </div>
  </div>
</div>

<!-- Card Daftar Mahasiswa -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between pb-0 mb-0">
    <h5>List Students</h5>
    <div class="d-flex gap-3">
      <a href="{{ route('courses.editStudent',[$course->slug, 'semester_id' => $semesterId] ) }}" class="btn btn-sm btn-warning">
        Edit Participant
      </a>
      <button class="btn btn-sm btn-outline-secondary" type="button"
        data-bs-toggle="collapse" data-bs-target="#filterCollapse"
        aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
      </button>
    </div>
  </div>

  <div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('courses.show', $course->slug) }}">
      <input type="hidden" name="semester_id" value="{{ $semesterId }}">
      <div class="mx-3 ">
        <div class="row g-2">
          <!-- Input Blok -->
          <div class="col-md-12">
            <label for="search" class="form-label mb-1">NIM/Nama</label>
            <input type="text" name="search" class="form-control form-control" placeholder="Filter NIM/Nama" value="{{ request('nim') }}">
          </div>

          <!-- Buttons -->
          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('courses.show', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
              class="btn btn-light btn-sm">Reset</a>
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
          @foreach($students as $student)
          @if(!request('search')
          || str_contains(strtolower($student->student->nim), strtolower(request('search')))
          || str_contains(strtolower($student->student->user->name), strtolower(request('search'))))
          <tr>
            <td class="align-middle text-center">{{ $student->student->nim }}</td>
            <td class="align-middle text-center">{{ $student->student->user->name }}</td>
            <td class="align-middle text-center">
              <a href="{{ route('courses.edit', $course->slug) }}"
                class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <a href="{{ route('courses.show', $course->slug) }}"
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

@endsection
@push('dashboard')