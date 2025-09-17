@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex flex-row justify-content-between">
        <div>
          <h5 class="mb-0">List {{ ucfirst($type ?? 'User') }}</h5>
        </div>
        <div class="d-flex gap-2">
          <!-- Tombol toggle collapse -->
          <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> Filter
          </button>

          <a href="{{ route('admin.users.create', $type ) }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
            + New {{ ucfirst($type ?? 'User') }}
          </a>
        </div>
      </div>

      <!-- Collapse Form -->
      <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('admin.users.index', $type) }}">
          <div class="mx-3 my-2 py-2">
            <div class="row g-2 align-items-end">

              @if($type === 'student')
              <div class="col-md-3">
                <label for="nim" class="form-label mb-1">NIM</label>
                <input type="text" class="form-control form-control-sm" name="nim" value="{{ request('nim') }}">
              </div>
              @elseif($type === 'lecturer')
              <div class="col-md-3">
                <label for="nidn" class="form-label mb-1">NIDN</label>
                <input type="text" class="form-control form-control-sm" name="nidn" value="{{ request('nidn') }}">
              </div>
              @endif

              <div class="col-md-3">
                <label for="name" class="form-label mb-1">Nama</label>
                <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}">
              </div>

              <div class="col-md-3">
                <label for="email" class="form-label mb-1">Email</label>
                <input type="text" class="form-control form-control-sm" name="email" value="{{ request('email') }}">
              </div>

              <div class="col-md-3 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index', $type) }}" class="btn btn-light btn-sm">Reset</a>
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
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  @if ($type === 'student')
                  <a href="{{ route('admin.users.index', ['type' => $type, 'sort' => 'nim', 'dir' => $sort === 'nim' && $dir === 'asc' ? 'desc' : 'asc']) }}">
                    NIM {!! $sort === 'nim' ? ($dir === 'asc' ? '↑' : '↓') : '' !!}
                  </a>
                  @else
                  <a href="{{ route('admin.users.index', ['type' => $type, 'sort' => 'nidn', 'dir' => $sort === 'nidn' && $dir === 'asc' ? 'desc' : 'asc']) }}">
                    NIDN {!! $sort === 'nidn' ? ($dir === 'asc' ? '↑' : '↓') : '' !!}
                  </a>
                  @endif
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ route('admin.users.index', ['type' => $type, 'sort' => 'name', 'dir' => $sort === 'name' && $dir === 'asc' ? 'desc' : 'asc']) }}">
                    Nama {!! $sort === 'name' ? ($dir === 'asc' ? '↑' : '↓') : '' !!}
                  </a>
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Email</th>

                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($users as $user)
              <tr>
                @if ($user->hasRole('student'))
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">{{ $user->student->nim ?? '-' }}</span>
                </td>
                @endif

                @if ($user->hasRole('lecturer'))
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">{{ $user->lecturer->nidn ?? '-' }}</span>
                </td>
                @endif
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold">{{ $user->name }}</span>
                </td>
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold">{{ $user->email }}</span>
                </td>

                <td class="align-middle text-center">
                  <a href="{{ route('admin.users.edit', [$type, $user->id]) }}"
                    class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                    <i class="fa-solid fa-pen "></i>
                  </a>

                  <a href="{{ route('admin.users.show', [$type, $user->id]) }}"
                    class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                    <i class="fas fa-info-circle "></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$users" />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('dashboard')