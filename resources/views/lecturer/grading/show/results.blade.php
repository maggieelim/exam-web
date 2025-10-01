<div class="card mt-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5>Student Results</h5>
    <!-- <span class="badge bg-primary">Total Students: {{ $exam->attempts_count }}</span> -->
  </div>
  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive p-0">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">NIM</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Nama</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Answered Questions</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Score</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($results as $result)
          <tr>
            <td class="align-middle text-center">{{ $result->student->student->nim }}</td>
            <td class="align-middle text-center">{{ $result->student->name }}</td>
            <td class="align-middle text-center">{{ $exam->questions_count }}/{{ $result->total_answered }}</td>
            <td class="align-middle">
              <div class="category-container" style="max-height: 120px; overflow-y: auto;">
                @foreach($result->categories_result as $cat)
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
              </div>
            </td>
            <td class="align-middle text-center">
              <a href="{{ route('lecturer.feedback', ['exam_code' => $exam->exam_code, 'nim' => $result->student->student->nim]) }}"
                class="btn bg-gradient-secondary m-1 p-2 px-3" title="Feedback">
                <i class="fas fa-info-circle"></i>
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