@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
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
                  <a href="{{ route('exams.index') }}?{{ http_build_query(array_merge(request()->except('page'), [
            'sort' => 'title',
            'dir'  => ($sort === 'title' && $dir === 'asc') ? 'desc' : 'asc'
        ])) }}"
                    class="text-dark text-decoration-none">
                    Title
                    @if($sort === 'title')
                    <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  Blok
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  Exam Questions
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  Score
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ route('exams.index') }}?{{ http_build_query(array_merge(request()->except('page'), [
            'sort' => 'exam_date',
            'dir'  => ($sort === 'exam_date' && $dir === 'asc') ? 'desc' : 'asc'
        ])) }}"
                    class="text-dark text-decoration-none">
                    Date
                    @if($sort === 'exam_date')
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
                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->title }}
                  </span>
                </td>

                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->course->name }}
                  </span>
                </td>

                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ $exam->questions_count > 0 ? $exam->questions_count . ' Questions' : 'No Questions Yet' }}
                  </span>
                </td>
                <td class="align-middle ">
                  @foreach($exam->categories_result as $result)
                  <div>
                    <span class="badge bg-primary">{{ $result['category_name'] }}</span>
                    {{ $result['percentage'] }}%
                  </div>
                  @endforeach
                </td>

                <td class="align-middle text-center">
                  <span class="text-sm font-weight-bold">
                    {{ \Carbon\Carbon::parse($exam->exam_date)->format('j/n/y H.i') }}
                  </span>
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
  </div>
  @endsection
  @push('dashboard')