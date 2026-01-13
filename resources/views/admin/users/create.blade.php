@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <!-- Import Section -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Import {{ strtoupper($session) }} {{ ucfirst($type) }} via Excel</h5>
      </div>
      <div class="card-body px-4 pt-2 pb-2">
        <form method="POST" action="{{ route('admin.users.import', $type) }}" enctype="multipart/form-data">
          @csrf
          <div class="row align-items-end">
            <div class="col-md-12 mb-3">
              <label class="form-label">Excel File</label>
              <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
            </div>
            <div class="col-md-2 mb-3">
              <a href="{{ route('admin.users.download-template', $type) }}" class="btn btn-sm bg-gradient-info w-100">
                <i class="fas fa-download"></i> Template
              </a>
            </div>
            <div class="col-md-2 mb-3">
              <button type="submit" class="btn btn-sm bg-gradient-success w-100">Import Data</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Create Form Section -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Create New {{ strtoupper($session) }} {{ ucfirst($type) }}</h5>
      </div>
      <div class="card-body px-4 pt-2 pb-2">
        <form method="POST" action="{{ route('admin.users.store', $type) }}">
          @csrf

          @include('admin.users.partials.user-form', ['user' => null, 'isCreate' => true])

          <div class="row mt-4">
            <div class="col-md-2">
              <button type="submit" class="btn btn-sm bg-gradient-primary w-100">Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection