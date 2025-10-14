@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="card mb-4">
    <div class="card-header d-flex flex-row justify-content-between pb-0 mb-0">
      <div>
        <h5 class="mb-0">Exams List</h5>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
          <i class="fas fa-filter"></i> Filter
        </button>
      </div>
    </div>

    <!-- Collapse Form -->
    <div class="collapse" id="filterCollapse">
      <form method="GET" action="{{ route('lecturer.results.index', $status) }}">
        <div class="mx-3 my-2 py-2">
          <div class="row g-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-4">
              <label for="semester_id" class="form-label mb-1">Semester</label>
              <select name="semester_id" id="semester_id" class="form-select">
                @foreach($semesters as $semester)
                <option value="{{ $semester->id }}"
                  {{ ($semesterId == $semester->id) ? 'selected' : '' }}>
                  {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                  @if($activeSemester && $semester->id == $activeSemester->id)
                  (Aktif)
                  @endif
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="title" class="form-label mb-1">Title</label>
              <input type="text" name="title" class="form-control" placeholder="Cari Judul Ujian"
                value="{{ request('title') }}">
            </div>
            <div class="col-md-4">
              <label for="blok" class="form-label mb-1">Blok</label>
              <select name="course_id" class="form-control">
                <option value="">-- Pilih Course --</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                  {{ $course->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
              <a href="{{ route('lecturer.results.index', $status) }}" class="btn btn-light btn-sm">Reset</a>
              <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="card-body px-0 pt-0 pb-2">
      <div class="table-responsive pb-5">
        <table class="table align-items-center mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-dark text-sm font-weight-bolder">
                <a class="text-dark text-decoration-none">
                  Exams
                  @if($sort === 'title')
                  <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                  @endif
                </a>
              </th>
              <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                Action
              </th>
            </tr>
          </thead>

          <tbody>
            @foreach($exams as $exam)
            <tr>
              <td class="align-middle px-3">
                <span class="text-sm font-weight-bold">
                  {{ $exam->title }} <br>
                  {{ $exam->course->name }} <br>
                </span>
                <span class="text-sm">
                  Modified at: {{ \Carbon\Carbon::parse($exam->updated_at)->format('j/n/y H.i') }} by {{ $exam->updater->name }}
                </span>
              </td>
              <td class="text-center">
                @if($exam->is_published)
                <a href="{{ route('lecturer.grade.published', [$exam->exam_code]) }}"
                  class="btn bg-gradient-success m-1 p-2 px-3" title="Info">
                  Graded </a>
                <a href="{{ route('lecturer.results.show.published', [$exam->exam_code]) }}" class="btn bg-gradient-primary m-1 p-2 px-3" title="Info">
                  <i class="fas fa-chart-line"></i> </a>
                @else
                <a href="{{ route('lecturer.grade.ungraded', [$exam->exam_code]) }}"
                  class="btn bg-gradient-info  m-1 p-2 px-3" title="Info">
                  Grade </a>
                <a href="{{ route('lecturer.results.show.ungraded', [$exam->exam_code]) }}" class="btn bg-gradient-primary m-1 p-2 px-3" title="Info">
                  <i class="fas fa-chart-line"></i> </a> @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
          <x-pagination :paginator="$exams" />
        </div>
      </div>
    </div>
  </div>

  @endsection
  @push('dashboard')