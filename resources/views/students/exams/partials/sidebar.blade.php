<div class="col-md-3">
  <div class="card p-3">
    {{-- Timer --}}
    <div class="d-flex justify-content-between align-items-center">
      <strong>SISA WAKTU</strong>
      <span id="timer" class="badge bg-danger fs-6">00:00:00</span>
    </div>

    <hr>

    {{-- Grid Navigasi Soal --}}
    <div class="grid-container pb-0" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 2px;">
      @foreach ($questions as $index => $q)
      @php
      $btnClass = 'btn-secondary';

      if ($q->id === $currentQuestionId) {
      $btnClass = 'border border-2 border-primary';
      } elseif (isset($doubtMap[$q->id])) {
      $btnClass = 'btn-warning';
      } elseif (isset($answeredMap[$q->id])) {
      $btnClass = 'btn-success';
      }
      @endphp

      <button type="button"
        class="btn btn-xs d-flex align-items-center justify-content-center question-nav {{ $btnClass }}"
        style="min-width: 28px; height: 33px; font-size: 0.9rem; padding: 0;" data-kode-soal="{{ $q->kode_soal }}"
        data-is-current="{{ $q->id === $currentQuestionId ? 'true' : 'false' }}">
        {{ $index + 1 }}</button>
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