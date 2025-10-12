<!-- Charts Section -->
<div class="row mb-4">
  <!-- Distribusi Skor -->
  <div class="col-md-4 mb-">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Score Distribution</h6>
      </div>
      <div class="card-body">
        <canvas id="scoreDistributionChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <!-- Tingkat Kesulitan Soal -->
  <div class="col-md-4 mb-">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Question Difficulty Level</h6>
      </div>
      <div class="card-body">
        <canvas id="difficultyChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <!-- Daya Pembeda -->
  <div class="col-md-4 mb-">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Discrimination Index</h6>
      </div>
      <div class="card-body">
        <canvas id="discriminationChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Analisis Per Soal -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center mb-0">
    <h5 class="card-title mb-0">
      Detailed Question Analysis </h5>
    <div class="d-flex justify-content-center align-items-center gap-3 my-0">
      <span class="badge bg-primary">{{ count($questionAnalysisPaginator) }} Questions</span>
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
          <input type="hidden" name="tab" value="analytics">
          <div class="col-md-12">
            <label for="difficulty_level" class="form-label mb-1">Question Difficulty</label>
            <select name="difficulty_level" id="difficulty_level" class="form-control">
              <option value="">-- All Levels --</option>
              @foreach($difficultyLevel as $level)
              <option value="{{ $level }}" {{ request('difficulty_level') == $level ? 'selected' : '' }}>
                {{ $level }}
              </option>
              @endforeach
            </select>
          </div>


          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=analytics"
              class="btn btn-light btn-sm">
              Reset
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
              Apply
            </button>
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
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">No</th>
            <th class="text-uppercase text-dark text-sm font-weight-bolder">Question</th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'correct_percentage', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'analytics']) }}">
                Correct
                @if(request('sort') === 'correct_percentage')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'difficulty_level', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'analytics']) }}">
                Difficulty
                @if(request('sort') === 'difficulty_level')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>
            <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'discrimination_index', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc', 'tab' => 'analytics']) }}">
                Discrimination Index
                @if(request('sort') === 'discrimination_index')
                <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
              </a>
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($questionAnalysisPaginator as $index => $analysis)
          <tr>
            <td class=" text-center">{{ $index + 1 }}</td>
            <td>
              <div class="question-preview">
                {!! Str::limit(strip_tags($analysis['question_text']), 50) !!}
              </div>
            </td>
            <td class="text-center">
              <small class="text-muted small d-block">
                {{ $analysis['correct_count'] }}/{{ $analysis['total_students'] }}
              </small>
              <span class="badge 
                                            {{ $analysis['correct_percentage'] >= 80 ? 'bg-gradient-success' : 
                                               ($analysis['correct_percentage'] >= 60 ? 'bg-gradient-info' : 
                                               ($analysis['correct_percentage'] >= 40 ? 'bg-gradient-warning' : 'bg-gradient-danger')) }}">
                {{ $analysis['correct_percentage'] }}%
              </span>
            </td>
            <td class="text-center">
              <span class="badge 
                                            {{ $analysis['difficulty_level'] == 'Easy' ? 'bg-gradient-success' : 
                                               ($analysis['difficulty_level'] == 'Medium' ? 'bg-gradient-info' : 
                                               ($analysis['difficulty_level'] == 'Fair' ? 'bg-gradient-warning' : 'bg-gradient-danger')) }}">
                {{ $analysis['difficulty_level'] }}
              </span>
            </td>
            <td class="text-center">
              <span class="badge 
                                            {{ $analysis['discrimination_index'] > 0.4 ? 'bg-gradient-success' : 
                                               ($analysis['discrimination_index'] >= 0.3 ? 'bg-gradient-info' : 
                                               ($analysis['discrimination_index'] >= 0.2 ? 'bg-gradient-warning' : 
                                               ($analysis['discrimination_index'] >= 0.1 ? 'bg-gradient-orange' : 'bg-gradient-danger'))) }}">
                {{ $analysis['discrimination_index'] }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-info-circle me-2"></i>Tidak ada data analisis yang tersedia
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
      <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$questionAnalysisPaginator" />
      </div>
    </div>
  </div>
</div>


<!-- Chart Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Data dari controller
    const chartData = @json($chartData ?? []);

    // Distribusi Skor Chart
    if (chartData.scores) {
      const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
      new Chart(scoreCtx, {
        type: 'bar',
        data: {
          labels: Object.keys(chartData.scores),
          datasets: [{
            label: 'Number of Students',
            data: Object.values(chartData.scores),
            backgroundColor: [
              '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: true,
              text: 'Score Distribution'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Students'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Score Range (%)'
              }
            }
          }
        }
      });
    }

    // Tingkat Kesulitan Chart
    if (chartData.difficulty) {
      const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
      new Chart(difficultyCtx, {
        type: 'doughnut',
        data: {
          labels: Object.keys(chartData.difficulty),
          datasets: [{
            data: Object.values(chartData.difficulty),
            backgroundColor: [
              '#198754', '#20c997', '#ffc107', '#dc3545'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Difficulty Level Distribution'
            }
          }
        }
      });
    }

    // Daya Pembeda Chart
    if (chartData.discrimination) {
      const discriminationCtx = document.getElementById('discriminationChart').getContext('2d');
      new Chart(discriminationCtx, {
        type: 'pie',
        data: {
          labels: Object.keys(chartData.discrimination),
          datasets: [{
            data: Object.values(chartData.discrimination),
            backgroundColor: [
              '#198754', '#20c997', '#ffc107', '#fd7e14', '#dc3545'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Discrimination Index Quality'
            }
          }
        }
      });
    }
  });
</script>