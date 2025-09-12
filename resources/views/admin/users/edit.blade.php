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
              <label>Tahun Ajaran</label>
              <input type="text" name="tahun_ajaran" class="form-control" value="{{ $user->student->tahun_ajaran }}" required>
            </div>
            <div class="col-md-3">
              <label>Kelas</label>
              <input type="text" name="kelas" class="form-control" value="{{ $user->student->kelas }}" required>
            </div>
            <div class="col-md-3">
              <label>Angkatan</label>
              <x-year-select name="angkatan" :selected="$user->student->angkatan ?? null" />
            </div>

          </div>
          @elseif ($type === 'lecturer')
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label>NIDN</label>
              <input type="text" name="nidn" class="form-control" value="{{ $user->lecturer->nidn }}" required>
            </div>
            <div class="col-md-6">
              <label>Faculty</label>
              <input type="text" name="faculty" class="form-control" value="{{ $user->lecturer->faculty }}" required>
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