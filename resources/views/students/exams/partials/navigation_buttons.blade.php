{{-- Tombol Navigasi --}}
<div class="mt-4 d-flex justify-content-center align-items-center">
    {{-- Tombol Sebelumnya --}}
    @if ($prevQuestion)
    <button type="button" id="prevQuestion" class="btn bg-gradient-primary"
        data-url="{{ route('student.exams.do', [$examData->exam_code, $prevQuestion->kode_soal]) }}">
        <i class="fas fa-chevron-left"></i>
    </button>
    @else
    <button type="button" class="btn btn-secondary" disabled>
        <i class="fas fa-chevron-left"></i>
    </button>
    @endif

    {{-- Checkbox Ragu-Ragu --}}
    <div class="form-check mx-3">
        <input class="form-check-input" type="checkbox" name="mark_doubt" id="markDoubtCheckbox" {{ isset($savedAnswer)
            && $savedAnswer->marked_doubt ? 'checked' : '' }}>
        <label class="form-check-label mb-0" for="markDoubtCheckbox">
            Tandai sebagai ragu-ragu
        </label>
    </div>

    {{-- Tombol Selanjutnya --}}
    @if ($nextQuestion)
    <button type="button" id="nextQuestion" class="btn bg-gradient-primary"
        data-url="{{ route('student.exams.do', [$examData->exam_code, $nextQuestion->kode_soal]) }}">
        <i class="fas fa-chevron-right"></i>
    </button>
    @else
    <button disabled type="button" id="nextQuestion" class="btn bg-gradient-primary">
        <i class="fas fa-chevron-right"></i>
    </button>
    @endif
</div>