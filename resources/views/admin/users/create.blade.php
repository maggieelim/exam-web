@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Create New {{ ucfirst($type) }}</h5>
      </div>


      <div class="card-body px-4 pt-2 pb-2">

        <form method="POST" action="{{ route('admin.users.import', $type) }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label>Import {{ ucfirst($type) }} via Excel</label>
            <input type="file" name="file" class="form-control" required>
          </div>
          <button type="submit" class="btn bg-gradient-success">Import</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="col-12 mb-4">

  <div class="card">
    <div class="card-header pb-0">
      <h5 class="mb-0">Create New {{ ucfirst($type) }}</h5>
    </div>
    <div class="card-body px-4 pt-2 pb-2">

      <form method="POST" action="{{ route('admin.users.store', $type) }}">
        @csrf
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-md-4 mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required autocomplete="off">
          </div>
          <div class="col-md-4 mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="new-password">
          </div>

          @if ($type === 'student')
          <div class="col-md-4 mb-3">
            <label>NIM</label>
            <input type="text" name="nim" class="form-control" required>
          </div>
          <div class="col-md-3 mb-3">
            <label>Angkatan</label>
            <input type="text" name="angkatan" class="form-control" required>
          </div>
          @elseif ($type === 'lecturer')
          <div class="col-md-3 mb-3">
            <label>NIDN</label>
            <input type="text" name="nidn" class="form-control" required>
          </div>
          @endif
        </div>
        <button type="submit" class="btn bg-gradient-primary">Save</button>
      </form>

      <hr>

    </div>
  </div>
</div>
</div>

@endsection