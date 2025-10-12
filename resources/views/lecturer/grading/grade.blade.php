@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4 p-3">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-3">{{ $exam->title }}</h5>
        <div>
          @if($exam->is_published)
          <button type="button" class="btn btn-sm btn-success" disabled>Published</button>
          @else
          <form action="{{ route('lecturer.results.publish', $exam->exam_code) }}" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Yakin ingin publish exam ini?')">
              Publish Exam
            </button>
          </form>
          @endif
          <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus course ini?')" class="d-inline">
            <button type="submit" class="btn btn-sm btn-warning">Print/Download</button>
          </form>
        </div>
      </div>
      <div class="row">

        <div class="col-md-6">
          <p><strong>Blok:</strong> {{ $exam->course->name }}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Participant:</strong> {{ $exam->attempts_count}}</p>
        </div>
        <div class="col-md-6 d-flex">
          <strong class="me-3">Dosen:</strong>
          <ul class="mb-0 ps-3">
            @foreach($exam->course->lecturers as $lecturer)
            <li>{{ $lecturer->name }}</li>
            @endforeach
          </ul>
        </div>
        <div class="col-md-6">
          <p><strong>Exam Date:</strong> {{ \Carbon\Carbon::parse($exam->exam_date)->format('j M Y') }}</p>
        </div>
      </div>
    </div>

    <!-- Card Daftar Mahasiswa -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between mb-0 pb-0">
        <h5>List Students</h5>
        <button class="btn btn-sm btn-outline-secondary" type="button"
          data-bs-toggle="collapse" data-bs-target="#filterCollapse"
          aria-expanded="false" aria-controls="filterCollapse">
          <i class="fas fa-filter"></i> Filter
        </button>
      </div>
      <div class="collapse" id="filterCollapse">
        <form method="GET"
          action="{{ route('lecturer.grade.' . $status, $exam->exam_code) }}">
          <input type="hidden" name="status" value="{{ $status }}">
          <div class="mx-3">
            <div class="row g-2">
              <!-- Input Blok -->
              <div class="col-md-12">
                <label for="blok" class="form-label mb-1">NIM/Name</label>
                <input type="text" name="name" class="form-control"
                  placeholder="Search Name or NIM"
                  value="{{ request('name') }}">
              </div>
              <!-- Buttons -->
              <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('lecturer.grade.' . $status, $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive ">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ request()->fullUrlWithQuery(['sort' => 'nim', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                    NIM
                    @if(request('sort') === 'nim')
                    <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                    Name
                    @if(request('sort') === 'name')
                    <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Answered Questions</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Score</th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                  <a href="{{ request()->fullUrlWithQuery(['sort' => 'feedback', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                    feedback
                    @if(request('sort') === 'feedback')
                    <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($results as $result)
              <tr>
                <td class="align-middle text-center">{{ $result['student']['student']['nim'] }}</td>
                <td class="align-middle text-center">{{ $result['student']['name'] }}</td>
                <td class="align-middle text-center">{{ $result['total_answered'] }}/{{ $exam->questions_count }}</td>
                <td class="align-middle">
                  <div class="category-container" style="max-height: 120px; overflow-y: auto;">
                    @foreach($result['categories_result'] as $cat)
                    <div class="d-flex align-items-center mb-2">
                      <span class="badge bg-light text-dark me-2" style="min-width: 120px; font-size: 0.75rem;">
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
                          title="{{ $cat['percentage'] }}% - {{ $cat['total_correct'] }}/{{ $cat['total_question'] }} correct">
                        </div>
                      </div>
                      <small class="ms-2 text-muted" style="min-width: 40px;">
                        {{ $cat['percentage'] }}%
                      </small>
                    </div>
                    @endforeach
                  </div>
                </td>
                <td class="align-middle text-center">{{ $result['feedback']}}</td>
                <td class="align-middle text-center">
                  <a href="{{ route('lecturer.feedback.'.$status, ['exam_code' => $exam->exam_code, 'nim' => $result['student']['student']['nim']]) }}"
                    class="btn bg-gradient-warning m-1 p-2 px-3" title="Feedback">
                    <i class="fas fa-comment"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$attempts" />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('dashboard')