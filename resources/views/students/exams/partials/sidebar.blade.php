<div class="card p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <strong>SISA WAKTU</strong>
    <span id="timer" class="badge bg-danger fs-6">00:00:00</span>
  </div>
  <hr>
  <div class="grid-container" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px;">
    @foreach($exam->questions->sortBy('id') as $index => $q)
    <button type="button"
      class="btn btn-xs d-flex align-items-center justify-content-center question-nav
      @if($q->id == $currentQuestion->id) border border-2 border-primary
      @elseif($q->isDoubtBy(auth()->id())) btn-warning
      @elseif(isset($userAnswers[$q->id]) && $userAnswers[$q->id] !== null) btn-success
      @else btn-secondary @endif"
      style="min-width: 28px; height: 35px; font-size: 0.9rem; padding: 0;"
      data-kode-soal="{{ $q->kode_soal }}"
      data-is-current="{{ $q->id == $currentQuestion->id ? 'true' : 'false' }}">
      {{ $loop->iteration }}
    </button>
    @endforeach
  </div>
  <hr>
  @if($allAnswered)
  <form action="{{ route('student.exams.finish', $exam->exam_code) }}" method="POST" id="finishForm">
    @csrf
    <button type="submit" class="btn btn-primary w-100">
      Selesaikan Ujian
    </button>
  </form>
  @endif
  <form id="autoFinishForm" action="{{ route('student.exams.finish', $exam->exam_code) }}" method="POST" style="display: none;">
    @csrf
  </form>
</div>