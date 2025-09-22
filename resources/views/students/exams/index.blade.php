@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex flex-row justify-content-between">
        <div>
          <h5 class="mb-0">Exams List</h5>
        </div>
        <a href="{{ route('exams.create') }}"
          class="btn bg-gradient-primary btn-sm mb-0"
          type="button">
          +&nbsp; Add Exam test
        </a>

      </div>
    </div>
    <div class="row">
      @foreach($exams as $exam)
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
          <div class="card-body p-3">
            <div class="row">
              <div class="d-flex flex-column h-100">
                <!-- Title -->
                <h5 class="font-weight-bolder">{{ $exam->title }}</h5>

                <!-- Exam type -->
                <p class="mb-1 text-secondary">
                  {{ $exam->examType->name }}
                </p>

                <!-- Date & time -->
                <p class="mb-1">
                  <i class="fas fa-calendar me-2"></i>
                  {{ \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y h:i A') }}
                </p>

                <!-- Duration -->
                <p class="mb-1">
                  <i class="fas fa-hourglass-half me-2"></i>
                  Duration: {{ $exam->duration }} minutes
                </p>

                <!-- Room -->
                <p class="mb-4">
                  <i class="fas fa-map-marker-alt me-2"></i>
                  Room: {{ $exam->room }}
                </p>

                <!-- Read more -->
                <a class="text-body text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:;">
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