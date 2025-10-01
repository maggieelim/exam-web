<div class="card p-3">
  <h5>SOAL NO. {{ $questionNumber }}</h5>
  <div class="mt-2">
    <p><strong>{{ $currentQuestion->badan_soal }}</strong></p>
    <p>{{ $currentQuestion->kalimat_tanya }}</p>
    @if($currentQuestion->image)
    <div class="my-3">
      <img src="{{ asset('storage/' . $currentQuestion->image) }}"
        alt="Gambar Soal"
        class="img-fluid"
        style="max-width: 600px;">
    </div>
    @endif
  </div>

  <form id="answerForm">
    @csrf
    <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">
    @foreach($currentQuestion->options as $option)
    <div class="form-check mt-3">
      <input class="form-check-input"
        type="radio"
        name="answer"
        id="option{{ $option->id }}"
        value="{{ $option->id }}"
        {{ isset($savedAnswer) && $savedAnswer->answer == $option->id ? 'checked' : '' }}>
      <label class="form-check-label" for="option{{ $option->id }}">
        {{ $option->text }}
      </label>
    </div>
    @endforeach

    <div class="mt-4 d-flex justify-content-between align-items-center">
      @if($prevQuestion)
      <button type="button" id="prevQuestion" class="btn btn-primary">Soal Sebelumnya</button>
      @else
      <button type="button" class="btn btn-secondary" disabled>Soal Sebelumnya</button>
      @endif

      <div class="form-check mx-3">
        <input class="form-check-input"
          type="checkbox"
          name="mark_doubt"
          id="markDoubtCheckbox"
          {{ isset($savedAnswer) && $savedAnswer->marked_doubt ? 'checked' : '' }}>
        <label class="form-check-label mb-0" for="markDoubtCheckbox">
          Tandai sebagai ragu-ragu
        </label>
      </div>

      @if($nextQuestion)
      <button type="button" id="nextQuestion" class="btn btn-primary">Soal Selanjutnya</button>
      @else
      <button type="button" id="finishExam" class="btn btn-success">Selesaikan Ujian</button>
      @endif
    </div>
  </form>
</div>