@extends('layouts.user_type.auth')

@section('content')

@hasrole('admin')
<div class="col-12">
  <h6 class="mb-3 text-uppercase">Users Overview</h6>
</div>
<div class="row">
  <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-capitalize font-weight-bolder">Total Admin</p>
              <h5 class="font-weight-bolder mb-0">
                {{ $totalAdmins }}
              </h5>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
              <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-capitalize font-weight-bolder">Total Lecturers</p>
              <h5 class="font-weight-bolder mb-0">
                {{ $totalLecturers }}
              </h5>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
              <i class="ni ni-hat-3 text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-capitalize font-weight-bolder">Total Students</p>
              <h5 class="font-weight-bolder mb-0">
                {{ $totalStudents }}
              </h5>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
              <i class="ni ni-badge text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row my-4">
  <div class="col-12">
    <h6 class="mb-3 text-uppercase">Semester Overview</h6>
  </div>

  {{-- Semester Aktif --}}
  <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-capitalize font-weight-bolder">Semester Aktif</p>
              <h6 class="font-weight-bolder mb-0">
                {{ $activeSemester->semester_name }} {{ $activeSemester->academicYear->year_name }}
              </h6>
              @if(!empty($activeSemester))
              <p class="text-secondary mb-0">
                {{ $semesterStart }} - {{ $semesterEnd }}
              </p>
              @endif
            </div>
          </div>
          <div class="col-4 text-end d-flex align-items-stretch justify-content-end">
            <div class="bg-gradient-info border-radius-lg w-50 h-100 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-white fs-1 opacity-10"></i>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endhasrole

@hasrole('student')
<div class="col-12 mb-4">
  <div class="card shadow-sm border-0">
    <div class="card-body px-4 py-4 d-flex justify-content-between align-items-center">

      <div>
        <h5 class="text-uppercase fw-bold mb-1">Welcome to the Student Portal</h5>
      </div>

      <div
        class="bg-gradient-primary text-white shadow-lg d-flex align-items-center justify-content-center rounded-circle"
        style="width: 60px; height: 60px;">
        <i class="ni ni-hat-3 text-lg opacity-10"></i>
      </div>

    </div>
  </div>
</div>
@endhasrole
@hasexactroles('lecturer')
<div class="col-12 mb-4">
  <div class="card shadow-sm border-0">
    <div class="card-body px-4 py-4 d-flex justify-content-between align-items-center">

      <div>
        <h5 class="text-uppercase fw-bold mb-1">Welcome to the Lecturer Portal</h5>
      </div>

      <div
        class="bg-gradient-primary text-white shadow-lg d-flex align-items-center justify-content-center rounded-circle"
        style="width: 60px; height: 60px;">
        <i class="ni ni-single-copy-04 text-lg opacity-10"></i>
      </div>

    </div>
  </div>
</div>
@endhasexactroles

@hasallroles('koordinator|lecturer')
<div class="col-12 mb-4">
  <h6 class="mb-3 text-uppercase">Overview Blok</h6>
</div>

<div class="row">
  @forelse($summary as $courseId => $data)
  @php
  $course = $data['course'];
  $totalSchedules = $data['total_schedules'];
  $scheduledCount = $data['scheduled_count'];
  $unscheduledCount = $data['unscheduled_count'];
  $activities = $data['activities'];
  $percentage = $totalSchedules > 0 ? ($scheduledCount / $totalSchedules) * 100 : 0;
  @endphp

  <div class="col-xl-6 col-sm-12 mb-4">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0 text-dark">
            {{ $course->name ?? 'Course ' . $courseId }}
          </h5>
          <span class="badge bg-gradient-info text-white px-3 py-2 rounded-pill">
            {{ number_format($percentage, 0) }}% progress
          </span>
        </div>

        {{-- Stats --}}
        <div class="row text-center">
          <div class="col">
            <h4 class="fw-bolder text-dark mb-0">{{ $totalSchedules }}</h4>
            <small class="text-secondary">Total</small>
          </div>
          <div class="col">
            <h4 class="fw-bolder text-success mb-0">{{ $scheduledCount }}</h4>
            <small class="text-secondary">Terjadwal</small>
          </div>
          <div class="col">
            <h4 class="fw-bolder text-warning mb-0">{{ $unscheduledCount }}</h4>
            <small class="text-secondary">Belum</small>
          </div>
        </div>

        {{-- Activities --}}
        <p class="fw-bold text-secondary mb-2">Aktivitas</p>
        <div class="row g-2">
          @foreach($activities as $activityId => $activityData)
          @php
          $activity = $activityData['activity'];
          $count = $activityData['count'];
          @endphp

          <div class="col-md-4 col-6">
            <div class="border rounded-3 p-1 bg-light">
              <div class="d-flex justify-content-between">
                <span class="text-sm fw-bold">{{ $activity->activity_name ?? 'Activity ' . $activityId }}</span>
                <span class="badge bg-primary text-white">{{ $count }}</span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        {{-- Button Action --}}
        <div class="mt-4">
          <a href="{{ route('courses.edit', ['course' => $course->slug, 'semester_id' => $activeSemester->id]) }}"
            class="btn bg-gradient-info btn-sm w-100 rounded-pill">
            <i class="fas fa-arrow-right me-1"></i> Lihat Detail Blok
          </a>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada jadwal blok</h5>
        <p class="text-sm text-muted">Silakan tambah jadwal terlebih dahulu.</p>
        <a href="{{ route('courses.index') }}" class="btn bg-gradient-info btn-sm  rounded-pill">
          Buat Jadwal Blok
        </a>
      </div>
    </div>
  </div>
  @endforelse
</div>
@endhasallroles
@endsection