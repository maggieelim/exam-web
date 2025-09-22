@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex flex-row justify-content-between">
        <div>
          <h5 class="mb-0">Courses List</h5>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> Filter
          </button>
          <a href="{{ route('courses.create' ) }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
            +&nbsp; New Course
          </a>
        </div>
      </div>

      <!-- Collapse Form -->
      <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('courses.index') }}">
          <div class="mx-3 my-2 py-2">
            <div class="row g-2">
              <!-- Input Blok -->
              <div class="col-md-6">
                <label for="blok" class="form-label mb-1">Blok</label>
                <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}">
              </div>

              <!-- Input Dosen -->
              <div class="col-md-6">
                <label for="dosen" class="form-label mb-1">Dosen</label>
                <input type="text" class="form-control form-control-sm" name="lecturer" value="{{ request('lecturer') }}">
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
                <th class="text-center">
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
                <th>
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
                <th class="text-center">Dosen</th>
                <th class="text-center">Students</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($courses as $course)
              <tr>
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold">{{ $course->kode_blok }}</span>
                </td>
                <td class="">
                  <span class=" text-sm font-weight-bold">{{ $course->name }}</span>
                </td>
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $course->lecturers->pluck('name')->join(', ') }}
                  </span>
                </td>
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold"> {{ $course->students->count() }}</span>
                </td>
                <td class="align-middle text-center">
                  <div class="btn-group">
                    <button type="button" class="btn bg-gradient-primary m-1 p-2 px-3 dropdown-toggle"
                      data-bs-toggle="dropdown" aria-expanded="false" title="Manage">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="{{ route('courses.edit', $course->slug) }}">
                          Kelola Blok
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('courses.editStudent',[$course->slug] ) }}">
                          Kelola Peserta Blok
                        </a>
                      </li>
                    </ul>
                  </div>

                  <a href="{{ route('courses.show', $course->slug) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle"></i>
                  </a>
                </td>
              </tr>
              @endforeach
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