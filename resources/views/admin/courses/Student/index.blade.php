@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex flex-row justify-content-between">
        <div>
          <h5 class="mb-0">Courses List</h5>
        </div>
        <a href=""
          class="btn bg-gradient-primary btn-sm mb-0"
          type="button">
          +&nbsp; Add course test
        </a>
      </div>
    </div>
    <div class="row">
      @foreach($course as $course)
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
          <div class="card-body p-3">
            <div class="row">
              <div class="d-flex flex-column h-100">
                <!-- Title -->
                <h6 class="font-weight-bolder">{{ $course->name }}</h6>

                <!-- course type -->
                <p class="mb-1 text-secondary">
                  {{ $course->kode_blok }}
                </p>

                <!-- Date & time -->
                <p class="mb-1">
                  <i class="fas fa-chalkboard-teacher me-2"></i>
                  {{ $course->lecturers->pluck('name')->join(', ') }}
                </p>


                <!-- Student -->
                <p class="mb-4">
                  <i class="fas fa-user me-2"></i>
                  Student: {{ $course->students->count() }}
                </p>

                <!-- Read more -->
                <a class="text-body text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="{{ route('admin.courses.editStudent',[$course->id] ) }}">
                  Read More
                  <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endsection
  @push('dashboard')