<div class="card mt-4">
  <div class="card-header mb-0 pb-0">
    <h5>Student Ranking & Results</h5>
  </div>
  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive p-0">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Rank</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">NIM</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Nama</th>
            <!-- <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Score</th> -->
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Progress</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Status</th>
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
            <!-- <td class="align-middle text-center">
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
            </td> -->

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

            <!-- Status -->
            <td class="align-middle text-center">
              <span class="badge 
                                @if($result['attempt_status'] == 'completed') bg-gradient-success
                                @elseif($result['attempt_status'] == 'in_progress') bg-gradient-warning
                                @elseif($result['attempt_status'] == 'idle') bg-gradient-secondary
                                @elseif($result['attempt_status'] == 'timedout') bg-gradient-danger
                                @else bg-info
                                @endif">
                {{ ucfirst($result['attempt_status']) }}
              </span>
              @if($result['completed_at'])
              <br>
              <small class="text-muted">
                {{ $result['completed_at']->format('M j, H:i') }}
              </small>
              @endif
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