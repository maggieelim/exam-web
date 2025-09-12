@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail {{ ucfirst($type) }}</h5>
        <a href="{{ route('admin.users.index', $type) }}" class="btn btn-sm btn-secondary">Kembali</a>
      </div>

      <div class="card-body px-4 pt-3 pb-3">
        <div class="row">
          <div class="col-md-6 mb-3">
            <strong>Nama:</strong>
            <p>{{ $user->name }}</p>
          </div>

          <div class="col-md-6 mb-3">
            <strong>Email:</strong>
            <p>{{ $user->email }}</p>
          </div>

          @if ($type === 'student' && $user->student)
          <div class="col-md-6 mb-3">
            <strong>NIM:</strong>
            <p>{{ $user->student->nim }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <strong>Tahun Ajaran:</strong>
            <p>{{ $user->student->tahun_ajaran }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <strong>Kelas:</strong>
            <p>{{ $user->student->kelas }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <strong>Angkatan:</strong>
            <p>{{ $user->student->angkatan }}</p>
          </div>
          @elseif ($type === 'lecturer' && $user->lecturer)
          <div class="col-md-6 mb-3">
            <strong>NIDN:</strong>
            <p>{{ $user->lecturer->nidn }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <strong>Faculty:</strong>
            <p>{{ $user->lecturer->faculty }}</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection