@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex flex-row justify-content-between align-items-center">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
          <h5 class="mb-0">Courses List</h5>

          {{-- Tampilkan semester aktif/terfilter di samping judul --}}
          @if($semesterId)
          @php
          $selectedSemester = $semesters->firstWhere('id', $semesterId);
          @endphp
          @if($selectedSemester)
          <span class="badge bg-info text-white">
            {{ $selectedSemester->semester_name }} - {{ $selectedSemester->academicYear->year_name }}
            @if($activeSemester && $selectedSemester->id == $activeSemester->id)
            (Aktif)
            @endif
          </span>
          @endif
          @endif
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" type="button"
            data-bs-toggle="collapse" data-bs-target="#filterCollapse"
            aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> Filter
          </button>

          <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
            +&nbsp; New Course
          </a>
        </div>
      </div>

      <!-- Collapse Form -->
      <div class="collapse {{ request()->hasAny(['semester_id', 'name', 'lecturer'])  }}" id="filterCollapse">
        <form method="GET" action="{{ route('courses.index') }}">
          <div class="mx-3 my-2 py-2">
            <div class="row g-2">
              <!-- Filter Semester (dari tabel semester) -->
              <div class="col-md-4">
                <label for="semester_id" class="form-label mb-1">Semester</label>
                <select name="semester_id" id="semester_id" class="form-select form-select-sm">
                  <option value="">-- Semua Semester --</option>
                  @foreach($semesters as $semester)
                  <option value="{{ $semester->id }}"
                    {{ ($semesterId == $semester->id) ? 'selected' : '' }}>
                    {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                    @if($activeSemester && $semester->id == $activeSemester->id)
                    (Aktif)
                    @endif
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Input Blok -->
              <div class="col-md-4">
                <label for="blok" class="form-label mb-1">Blok</label>
                <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}" placeholder="Kode atau nama blok">
              </div>

              <!-- Input Dosen -->
              <div class="col-md-4">
                <label for="dosen" class="form-label mb-1">Dosen</label>
                <input type="text" class="form-control form-control-sm" name="lecturer" value="{{ request('lecturer') }}" placeholder="Nama dosen">
              </div>

              <!-- Buttons -->
              <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('courses.index') }}" class="btn btn-light btn-sm">Reset</a>
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
                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">
                  <a href="{{ route('courses.index', array_merge(request()->all(), [
                              'sort' => 'kode_blok',
                              'dir' => ($sort === 'kode_blok' && $dir === 'asc') ? 'desc' : 'asc'
                              ])) }}">
                    Kode Blok
                    @if($sort === 'kode_blok')
                    <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ route('courses.index', array_merge(request()->all(), [
          'sort' => 'name',
          'dir' => ($sort === 'name' && $dir === 'asc') ? 'desc' : 'asc'
      ])) }}">
                    Nama
                    @if($sort === 'name')
                    <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Total Students</th>
                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Action</th>
              </tr>
            </thead>

            <tbody>
              @forelse ($courses as $course)
              <tr>
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">{{ $course->kode_blok }}</span>
                </td>
                <td class="">
                  <span class="text-sm font-weight-bold">{{ $course->name }}</span>
                </td>
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">{{ $course->student_count ?? 0 }}</span>
                </td>
                <td class="align-middle text-center">
                  <div class="btn-group">
                    <button type="button" class="btn bg-gradient-primary m-1 p-2 px-3 dropdown-toggle"
                      data-bs-toggle="dropdown" aria-expanded="false" title="Manage">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                    <ul class="dropdown-menu shadow">
                      <li>
                        <a class="dropdown-item"
                          href="{{ route('courses.edit', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                          <i class="fas fa-cog text-secondary me-2"></i> Kelola Blok
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item"
                          href="{{ route('courses.editStudent', ['slug' => $course->slug, 'semester_id' => $semesterId]) }}">
                          <i class="fas fa-users text-secondary me-2"></i> Kelola Peserta Blok
                        </a>
                      </li>
                    </ul>
                  </div>
                  <a href="{{ route('courses.show', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div class="text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Tidak ada course yang ditemukan</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-primary">Reset Filter</a>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$courses" />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('dashboard')
@endpush