<div class="d-flex justify-content-end align-items-center p-0 mt-2">
  <button class="btn btn-sm btn-outline-secondary" type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filterCollapse"
    aria-expanded="false"
    aria-controls="filterCollapse">
    <i class="fas fa-filter me-1"></i> Filter
  </button>
</div>

<div class="collapse" id="filterCollapse">
  <form method="GET" action="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}">
    <div class="mx-3 mb-2 pb-2">
      <div class="row g-2">
        <input type="hidden" name="status" value="{{ $status }}" class="m-0 p-0">
        <input type="hidden" name="tab" value="answers" class="m-0 p-0">
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
          <a href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=answers"
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

@forelse($questionAnalysisPaginator as $analysis)
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
      <!-- Benar -->
      <div class="d-flex align-items-center">
        <span class="fw-semibold">Correct:</span>
        <span class="ms-2">{{ $analysis['correct_count'] }}/{{ $analysis['total_students'] }}</span>
        <span class="badge ms-2 
            {{ $analysis['correct_percentage'] >= 80 ? 'bg-gradient-success' : 
              ($analysis['correct_percentage'] >= 60 ? 'bg-gradient-info' : 
              ($analysis['correct_percentage'] >= 40 ? 'bg-gradient-warning' : 'bg-gradient-danger')) }}">
          {{ $analysis['correct_percentage'] }}%
        </span>
      </div>

      <!-- Daya Pembeda -->
      <div class="d-flex align-items-center">
        <span class="fw-semibold">Discrimination Index:</span>
        <span class="badge ms-2 
            {{ $analysis['discrimination_index'] > 0.4 ? 'bg-gradient-success' : 
              ($analysis['discrimination_index'] >= 0.3 ? 'bg-gradient-info' : 
              ($analysis['discrimination_index'] >= 0.2 ? 'bg-gradient-warning' : 
              ($analysis['discrimination_index'] >= 0.1 ? 'bg-gradient-orange' : 'bg-gradient-danger'))) }}">
          {{ $analysis['discrimination_index'] }}
        </span>
      </div>

      <!-- Tingkat Kesulitan -->
      <div class="d-flex align-items-center">
        <span class="fw-semibold">Difficulty:</span>
        <span class="badge ms-2 
            {{ $analysis['difficulty_level'] == 'Easy' ? 'bg-gradient-success' : 
              ($analysis['difficulty_level'] == 'Medium' ? 'bg-gradient-info' : 
              ($analysis['difficulty_level'] == 'Fair' ? 'bg-gradient-warning' : 'bg-gradient-danger')) }}">
          {{ $analysis['difficulty_level'] }}
        </span>
      </div>
    </div>

    <div class="mb-3">
      @if(!empty($analysis['question_text']))
      <p class="fw-bold mb-1">{{ $analysis['question_text'] }}</p>
      @endif
      @if(!empty($analysis['question']))
      <p class="text-muted mb-0">{{ $analysis['question'] }}</p>
      @endif
    </div>

    @if($analysis['image'])
    <div class="my-3">
      <img src="{{ asset('storage/' . $analysis['image']) }}"
        alt="Gambar Soal"
        class="img-fluid rounded shadow-sm"
        style="max-width: 400px;">
    </div>
    @endif

    @if(!empty($optionsAnalysis[$analysis['question_id']]))
    <div class="row mt-3">
      @foreach($optionsAnalysis[$analysis['question_id']] as $optionIndex => $option)
      <div class="col-12 d-flex align-items-center mb-2">
        <div class="fw-bold me-2">{{ chr(65 + $optionIndex) }}.</div>
        <div class="me-2">{{ $option['option_text'] ?? '' }}</div>

        @if(!empty($option['is_correct']))
        <div class="text-success me-2">✔</div>
        @endif

        <div class="me-2 badge bg-light text-dark">
          {{ $option['percentage'] ?? 0 }}%
        </div>
        <div class="text-muted">
          ({{ $option['count'] ?? 0 }} siswa)
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @else
  <div class="alert alert-warning m-2">
    Tidak ada data analisis untuk soal ini.
  </div>
  @endif
</div>
@empty
<div>
  <div class="text-center text-muted py-4">
    <i class="fas fa-info-circle me-2"></i>Tidak ada data analisis yang tersedia
  </div>
</div>
@endforelse

<div class="d-flex justify-content-center mt-3">
  <x-pagination :paginator="$questionAnalysisPaginator" />
</div>