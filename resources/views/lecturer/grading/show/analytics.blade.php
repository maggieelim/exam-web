<div class="row my-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-chart-bar me-2"></i>Statistik Umum Ujian
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-primary">{{ $analytics['total_students'] ?? 0 }}</h3>
              <p class="text-muted mb-0">Total Peserta</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-success">{{ number_format($analytics['average_score'] ?? 0, 1) }}%</h3>
              <p class="text-muted mb-0">Rata-rata Nilai</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-info">{{ number_format($analytics['highest_score'] ?? 0, 1) }}%</h3>
              <p class="text-muted mb-0">Nilai Tertinggi</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-warning">{{ number_format($analytics['completion_rate'] ?? 0, 1) }}%</h3>
              <p class="text-muted mb-0">Tingkat Penyelesaian</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
  <!-- Distribusi Skor -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Distribusi Skor</h6>
      </div>
      <div class="card-body">
        <canvas id="scoreDistributionChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <!-- Tingkat Kesulitan Soal -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Tingkat Kesulitan Soal</h6>
      </div>
      <div class="card-body">
        <canvas id="difficultyChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <!-- Daya Pembeda -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">Indeks Daya Pembeda</h6>
      </div>
      <div class="card-body">
        <canvas id="discriminationChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Analisis Per Soal -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="fas fa-analytics me-2"></i>Analisis Detail Per Soal
        </h5>
        <span class="badge bg-primary">{{ count($questionAnalysis) }} Soal</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th width="5%">No</th>
                <th width="35%">Soal</th>
                <th width="10%" class="text-center">% Benar</th>
                <th width="15%" class="text-center">Tingkat Kesulitan</th>
                <th width="15%" class="text-center">Daya Pembeda</th>
              </tr>
            </thead>
            <tbody>
              @forelse($questionAnalysis as $index => $analysis)
              <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                  <div class="question-preview">
                    {!! Str::limit(strip_tags($analysis['question_text']), 100) !!}
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge 
                                            {{ $analysis['correct_percentage'] >= 80 ? 'bg-success' : 
                                               ($analysis['correct_percentage'] >= 60 ? 'bg-info' : 
                                               ($analysis['correct_percentage'] >= 40 ? 'bg-warning' : 'bg-danger')) }}">
                    {{ $analysis['correct_percentage'] }}%
                  </span>
                  <br>
                  <small class="text-muted">
                    {{ $analysis['correct_count'] }}/{{ $analysis['total_students'] }}
                  </small>
                </td>
                <td class="text-center">
                  <span class="badge 
                                            {{ $analysis['difficulty_level'] == 'Mudah' ? 'bg-success' : 
                                               ($analysis['difficulty_level'] == 'Sedang' ? 'bg-info' : 
                                               ($analysis['difficulty_level'] == 'Cukup Sulit' ? 'bg-warning' : 'bg-danger')) }}">
                    {{ $analysis['difficulty_level'] }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge 
                                            {{ $analysis['discrimination_index'] > 0.4 ? 'bg-success' : 
                                               ($analysis['discrimination_index'] >= 0.3 ? 'bg-info' : 
                                               ($analysis['discrimination_index'] >= 0.2 ? 'bg-warning' : 
                                               ($analysis['discrimination_index'] >= 0.1 ? 'bg-orange' : 'bg-danger'))) }}">
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
        </div>
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
            label: 'Jumlah Siswa',
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
              text: 'Distribusi Skor Siswa'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah Siswa'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Rentang Skor (%)'
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
              text: 'Distribusi Tingkat Kesulitan'
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
              text: 'Kualitas Daya Pembeda'
            }
          }
        }
      });
    }
  });
</script>