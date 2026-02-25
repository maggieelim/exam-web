<div class="col-md-3">
  <div class="card p-3">
    {{-- Timer --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <strong>SISA WAKTU</strong>
      <span id="timer" class="badge bg-danger fs-6">00:00:00</span>
    </div>

    <hr>

    {{-- Grid Navigasi Soal --}}
    <div class="grid-container" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px;">
      @foreach ($questions as $index => $q)
      <button type="button" class="btn btn-xs d-flex align-items-center justify-content-center question-nav
                        @if ($q->id == $currentQuestion->id) border border-2 border-primary
                        @elseif($q->isDoubtBy(auth()->id())) btn-warning
                        @elseif(isset($userAnswers[$q->id]) && $userAnswers[$q->id] !== null) btn-success
                        @else btn-secondary 
                        @endif" style="min-width: 28px; height: 35px; font-size: 0.9rem; padding: 0;"
        data-kode-soal="{{ $q->kode_soal }}" data-is-current="{{ $q->id == $currentQuestion->id ? 'true' : 'false' }}">
        {{ $loop->iteration }}
      </button>
      @endforeach
    </div>

    <hr>

    {{-- Tombol Selesaikan Ujian --}}
    <div id="finishExamContainer" style="display: {{ $allAnswered ? 'block' : 'none' }};">
      <form action="{{ route('student.exams.finish', $examData->exam_code) }}" method="POST" id="finishForm">
        @csrf
        <button type="submit" class="btn btn-success w-100">
          Selesaikan Ujian
        </button>
      </form>
    </div>

    {{-- Form Auto Finish --}}
    <form id="autoFinishForm" action="{{ route('student.exams.finish', $examData->exam_code) }}" method="POST"
      style="display: none;">
      @csrf
      <input type="hidden" name="auto_finish" value="1">
    </form>
  </div>
</div>