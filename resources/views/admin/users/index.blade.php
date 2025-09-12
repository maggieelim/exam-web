@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex flex-row justify-content-between">
        <div>
          <h5 class="mb-0">List Users</h5>
        </div>
        <a href="{{ route('admin.users.create', $type ) }}"
          class="btn bg-gradient-primary btn-sm mb-0"
          type="button">
          +&nbsp; New {{ ucfirst($type ?? 'User') }}
        </a>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder ">Nama</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder  ">Email</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  @if ($type === 'student')
                  NIM
                  @else ($type === 'lecturer')
                  NIDN
                  @endif
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder ">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $user)
              <tr>
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold">{{ $user->name }}</span>
                </td>
                <td class="align-middle text-center">
                  <span class=" text-sm font-weight-bold">{{ $user->email }}</span>
                </td>
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
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('dashboard')