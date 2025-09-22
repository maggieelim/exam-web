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
          <button type="submit" class="btn bg-gradient-success">Import</button>
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
        <div class="mb-3">
          <label>Kode Blok</label>
          <input type="text" name="kode_blok" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Nama Blok</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Cover Blok</label>
          <input type="file" name="cover" class="form-control" accept="image/*">
        </div>


        <button type="submit" class="btn bg-gradient-primary">Save</button>
      </form>

      <hr>

    </div>
  </div>
</div>
</div>

@endsection