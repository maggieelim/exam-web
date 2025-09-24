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
        <div class="row">
          <div class="mb-3 col-md-4">
            <label>Kode Blok</label>
            <input type="text" name="kode_blok" class="form-control" required>
          </div>
          <div class="mb-3 col-md-4">
            <label>Nama Blok</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3 col-md-4">
            <label>Cover Blok</label>
            <input type="file" name="cover" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-12">
            <label>Dosen Pengajar</label>
            <select id="lecturers" name="lecturers[]" multiple class="btn-primary">
              @foreach($lecturers as $lecturer)
              <option value="{{ $lecturer->id }}"
                @if(auth()->user()->hasRole('lecturer') && auth()->id() == $lecturer->id) selected @endif>
                {{ $lecturer->name }}
              </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-2">
            <button type="submit" class="btn bg-gradient-primary">Save</button>
          </div>
        </div>
      </form>

      <hr>

    </div>
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