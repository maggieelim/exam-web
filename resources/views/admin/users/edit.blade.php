@extends('layouts.user_type.auth')

@section('content')
<div class="row">

  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Edit {{ ucfirst($type) }}</h5>
      </div>
      <div class="card-body px-4 pt-2 pb-2">
        <form method="POST" action="{{ route('admin.users.update', [$type, $user->id]) }}">
          @csrf

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label>Name</label>
              <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>
            <div class="col-md-4">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>
            <div class="col-md-4">
              <label>Password <small>(kosongkan jika tidak diubah)</small></label>
              <input type="password" name="password" class="form-control">
            </div>
          </div>

          @if ($type === 'student')
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label>NIM</label>
              <input type="text" name="nim" class="form-control" value="{{ $user->student->nim }}" required>
            </div>
            <div class="col-md-3">
              <label>Angkatan</label>
              <x-year-select name="angkatan" :selected="$user->student->angkatan ?? null" />
            </div>

          </div>
          @elseif ($type === 'lecturer')
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label>NIDN</label>
              <input type="text" name="nidn" class="form-control" value="{{ optional($user->lecturer)->nidn ?? '' }}" required>
            </div>
            <div class="col-md-4">
              <label for="role" class="form-label">Role</label>
              <select name="role" id="role" class="form-select" required>
                @foreach($roles as $id => $name)
                <option value="{{ $name }}" {{ $user->hasRole($name) ? 'selected' : '' }}>
                  {{ ucfirst($name) }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          @endif

          <div class="row">
            <div class="col-md-2">
              <button type="submit" class="btn bg-gradient-primary w-100">Update</button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>

</div>
@endsection