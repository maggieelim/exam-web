@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail {{ ucfirst($type) }}</h5>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.users.index', $type) }}" class="btn btn-sm btn-secondary">Back</a>
          <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
            Delete
          </button>

        </div>
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

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Hapus User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" action="{{ route('admin.users.destroy', [$type, $user->id]) }}">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection