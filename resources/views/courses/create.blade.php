@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Create New Course</h5>
      </div>


      <div class="card-body px-4 pt-2 pb-2">

        <form method="POST" action="{{ route('courses.import') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label>Import Course via Excel</label>
            <input type="file" name="file" class="form-control" required>
          </div>
          <a href="{{ asset('templates/template_import_blok.xlsx') }}"
            class="btn btn-info btn-sm"
            download>
            <i class="fas fa-download me-1"></i>Download Template
          </a>
          <button type="submit" class="btn btn-sm bg-gradient-success">Import</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="col-12 mb-4">

  <div class="card">
    <div class="card-header pb-0">
      <h5 class="mb-0">Create New Course</h5>
    </div>
    <div class="card-body px-4 pt-2 pb-2">

      <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
          <div class="mb-3 col-md-6">
            <label>Kode Blok</label>
            <input type="text" name="kode_blok" class="form-control" required>
            <!-- Dalam form create -->
          </div>
          <div class="mb-3 col-md-6">
            <label>Nama Blok</label>
            <input type="text" name="name" class="form-control" required>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-12">
            <label>Semester</label>
            <select id="semester" name="semester" class="form-select form-select">
              <option value="Ganjil/Genap">Ganjil/Genap</option>
              <option value="Ganjil"> Ganjil</option>
              <option value="Genap">Genap</option>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-2">
            <button type="submit" class="btn bg-gradient-primary">Save</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@push('dashboard')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const multipleSelect = new Choices('#lecturers', {
      removeItemButton: true,
      searchEnabled: true
    });
  });
</script>
@endpush