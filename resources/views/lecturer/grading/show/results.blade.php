<div class="card mt-4">
  <div class="card-header d-flex flex-row justify-content-between mb-0 pb-0">
    <h5>Student Ranking & Results</h5>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
      </button>
    </div>
  </div>
  <div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}">
      <div class="mx-3 mb-2 pb-2">
        <div class="row g-2">
          <input type="hidden" name="status" value="{{ $status }}">
          <div class="col-md-12">
            <label for="name" class="form-label mb-1">Name/NIM</label>
            <input type="text" name="name" class="form-control" placeholder="Student name / NIM"
              value="{{ request('name') }}">
          </div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
          </div>
        </div>
      </div>
    </form>

  </div>

  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive p-0">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'rank', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'results']) }}"
                class="text-dark text-decoration-none">
                Rank
                @if(request('sort') === 'rank')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>

            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'nim', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'results']) }}"
                class="text-dark text-decoration-none">
                NIM
                @if(request('sort') === 'nim')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>

            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'results']) }}"
                class="text-dark text-decoration-none">
                Nama
                @if(request('sort') === 'name')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>

            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'score', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'results']) }}"
                class="text-dark text-decoration-none">
                Score
                @if(request('sort') === 'score')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>

            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Competency Level</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($rankingPaginator as $result)
          <tr>
            <!-- Rank -->
            <td class="text-center">
              @if($result['rank'] == 1)
              <span class="badge bg-warning text-dark">
                <i class="fas fa-trophy me-1"></i>1
              </span>
              @elseif($result['rank'] == 2)
              <span class="badge bg-secondary">
                <i class="fas fa-medal me-1"></i>2
              </span>
              @elseif($result['rank'] == 3)
              <span class="badge" style="background-color: orange;">
                <i class="fas fa-medal me-1"></i>3
              </span>
              @else
              <span class="badge bg-light text-dark">
                {{ $result['rank'] }}
              </span>
              @endif

              <!-- NIM -->
            <td class="align-middle text-center">
              <span class="text-sm font-weight-bold">
                {{ $result['student_data']->nim ?? 'N/A' }}
              </span>
            </td>

            <!-- Nama -->
            <td class="align-middle text-center">
              <span class="text-sm font-weight-bold">
                {{ $result['student']->name }}
              </span>
            </td>

            <!-- Score -->
            <td class="align-middle text-center">
              <div class="d-flex flex-column align-items-center">
                <span class="text-lg font-weight-bold 
                                    @if($result['score_percentage'] >= 80) text-success
                                    @elseif($result['score_percentage'] >= 60) text-info
                                    @elseif($result['score_percentage'] >= 40) text-warning
                                    @else text-danger
                                    @endif">
                  {{ $result['score_percentage'] }}%
                </span>
                <small class="text-muted">
                  {{ $result['correct_answers'] }}/{{ $result['total_questions'] }}
                </small>
              </div>
            </td>

            <!-- Progress per Kategori -->
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

            <!-- Action -->
            <td class="align-middle text-center">
              <input type="hidden" name="status" value="{{ $status }}">
              <a href="{{ route('lecturer.feedback.' .$status, ['exam_code' => $exam->exam_code, 'nim' => $result['student_data']->nim ?? '']) }}"
                class="btn bg-gradient-info btn-sm m-1" title="Feedback & Details">
                <i class="fas fa-info-circle me-1"></i> Details
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <i class="fas fa-users-slash fa-2x mb-2"></i><br>
              No students have attempted this exam yet.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
      <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$rankingPaginator" />
      </div>
    </div>
  </div>
</div>