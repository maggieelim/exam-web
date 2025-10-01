@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 card mb-4 p-3">
    <div class="card-header d-flex flex-row justify-content-between">
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
            <div class="col-md-2">
              <label for="start_date" class="form-label mb-1">Start Date</label>
              <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
              <label for="end_date" class="form-label mb-1">End Date</label>
              <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
              <a href="{{ route('exams.index') }}" class="btn btn-light btn-sm">Reset</a>
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
              <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                <a class="text-dark text-decoration-none">
                  Exam
                </a>
              </th>

              <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                Score
              </th>
              <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                Action
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
                  Exam Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('j/n/y H.i') }}
                </span>
              </td>
              <td class="align-middle ">
                @foreach($exam->categories_result as $cat)
                <div class="d-flex align-items-center mb-2">
                  <span class="badge bg-light text-dark me-2" style="min-width: 120px; max-height:30px; font-size: 0.75rem;">
                    {{ Str::limit($cat['category_name'], 20) }}
                  </span>
                  <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
                    <div class="progress-bar m-0
                        @if($cat['percentage'] == 0) bg-secondary opacity-50
                        @elseif($cat['percentage'] >= 80) bg-success
                        @elseif($cat['percentage'] >= 60) bg-info
                        @elseif($cat['percentage'] >= 40) bg-warning
                        @else bg-danger
                        @endif"
                      role="progressbar"
                      style="width: {{ max($cat['percentage'], 1) }}%"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="@if($cat['percentage'] == 0)Tidak ada jawaban benar@else{{ $cat['percentage'] }}%@endif">
                    </div>
                  </div>
                  <small class="ms-2 text-muted" style="min-width: 40px;">
                    {{ $cat['percentage'] }}%
                  </small>
                </div>
                @endforeach
              </td>
              <td class="align-middle text-center">
                <a href="{{ route('exams.show', [$exam->exam_code]) }}"
                  class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                  <i class="fas fa-info-circle"></i>
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