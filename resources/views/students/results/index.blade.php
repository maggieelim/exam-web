@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 card mb-4 p-3">
    <div class="card-header d-flex flex-row justify-content-between p-0 m-0">
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
      <form method="GET" action="">
        <div class="mx-3 my-2 py-2">
          <div class="row g-2">
            <div class="col-md-6">
              <label for="title" class="form-label mb-1">Title</label>
              <input type="text" name="title" class="form-control" placeholder="Cari Judul Ujian"
                value="{{ request('title') }}">
            </div>
            <div class="col-md-6">
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
              <a href="{{ route('student.results.index') }}" class="btn btn-light btn-sm">Reset</a>
              <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="card-body px-0 pt-0">
      <div class="table-responsive">
        <table class="table align-items-center mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-dark text-sm font-weight-bolder">
                <a class="text-dark text-decoration-none">
                  Exam
                </a>
              </th>
            </tr>
          </thead>

          <tbody>
            @foreach($exams as $exam)
            <tr>
              <td class="align-middle">
                <span class="text-sm font-weight-bold">
                  {{ $exam->title }} <br>
                  {{ $exam->course->name }} <br>
                </span>
                <span class="text-sm">
                  Exam Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('j/n/y') }}
                </span>
              </td>
              <td class="text-end">
                <a href="{{ route('student.results.show', $exam->exam_code) }}"
                  class="btn bg-gradient-success m-1 p-2 px-3" title="Results">
                  <i class="fas fa-chart-bar me-2"></i> Results
                </a>
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