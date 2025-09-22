@extends('layouts.user_type.auth')

@section('content')
<div class="row">

  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Edit Course</h5>
      </div>
      <div class="card-body px-4 pt-2 pb-2">
        <form method="POST" action="{{ route('courses.update', $course->slug) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label>Kode Blok</label>
              <input type="text" name="kode_blok" class="form-control" value="{{ $course->kode_blok }}" required>
            </div>
            <div class="col-md-4">
              <label>Nama Blok</label>
              <input type="text" name="name" class="form-control" value="{{ $course->name }}" required>
            </div>
            <div class="col-md-4">
              <label>Cover Blok</label>
              <input type="file" name="cover" class="form-control" accept="image/*">
              @if($course->cover)
              <img src="{{ asset('storage/' . $course->cover) }}"
                alt="Cover"
                style="max-width: 200px; height: auto; border-radius: 5px; position:absolute; margin:10px">
              @endif
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Dosen Pengajar</label>
              <select id="lecturers" name="lecturers[]" multiple class="btn-primary">
                @foreach($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}"
                  @if(in_array($lecturer->id, $course->lecturers->pluck('id')->toArray())) selected @endif>
                  {{ $lecturer->name }}
                </option>
                @endforeach
              </select>
            </div>
          </div>

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