@extends('layouts.user_type.auth')

@section('hideSidebar')@endsection
@section('hideNavbar')@endsection
@section('hideFooter')@endsection

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-md-9">
      <div class="card p-3">
        <h5>SOAL NO. {{ $questionNumber }}</h5>
        <div class="mt-2">
          <p><strong>{{ $currentQuestion->badan_soal }}</strong></p>
          <p>{{ $currentQuestion->kalimat_tanya }}</p>
        </div>

        {{-- Form AJAX --}}
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
            @if($prevQuestionId)
            <button type="button" id="prevQuestion" class="btn btn-primary">Soal Sebelumnya</button>
            @else
            <button type="button" class="btn btn-secondary" disabled>Soal Sebelumnya</button>
            @endif

            {{-- Checkbox Ragu-Ragu --}}
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

            @if($nextQuestionId)
            <button type="button" id="nextQuestion" class="btn btn-primary">Soal Selanjutnya</button>
            @else
            <button type="button" id="nextQuestion" class="btn btn-success" disabled>Soal Selanjutnya</button>
            @endif
          </div>
        </form>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <strong>SISA WAKTU</strong>
          <span id="timer" class="badge bg-danger fs-6">00:00:00</span>
        </div>
        <hr>
        <div class="grid-container" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px;">
          @foreach($exam->questions as $q)
          <button type="button"
            class="btn btn-xs d-flex align-items-center justify-content-center question-nav
            @if($q->id == $currentQuestion->id) border border-2 border-primary
            @elseif($q->isDoubtBy(auth()->id())) btn-warning
      @elseif(isset($userAnswers[$q->id]) && $userAnswers[$q->id] !== null) btn-success
            @else btn-secondary @endif"
            style="min-width: 28px; height: 35px; font-size: 0.9rem; padding: 0;"
            data-question-id="{{ $q->id }}"
            data-is-current="{{ $q->id == $currentQuestion->id ? 'true' : 'false' }}"> {{ $loop->iteration }}
          </button>
          @endforeach
        </div>
        <hr>
        @if($allAnswered)
        <form action="{{ route('student.exams.finish', $exam->id) }}" method="POST" id="finishForm">
          @csrf
          <button type="submit" class="btn btn-primary w-100"
            onclick="return confirm('Apakah Anda yakin ingin menyelesaikan ujian?')">
            Selesaikan Ujian
          </button>
        </form>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Timer code (tetap sama)
    const endTime = new Date("{{ $endTime }}").getTime();
    if (!localStorage.getItem('examEndTime_{{ $exam->id }}')) {
      localStorage.setItem('examEndTime_{{ $exam->id }}', endTime);
    }

    const savedEndTime = parseInt(localStorage.getItem('examEndTime_{{ $exam->id }}'));

    function updateTimer() {
      var now = new Date().getTime();
      var distance = savedEndTime - now;

      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      document.getElementById("timer").innerHTML =
        ("0" + hours).slice(-2) + ":" +
        ("0" + minutes).slice(-2) + ":" +
        ("0" + seconds).slice(-2);

      if (distance < 0) {
        clearInterval(timerInterval);
        document.getElementById("timer").innerHTML = "WAKTU HABIS";
        localStorage.removeItem('examEndTime_{{ $exam->id }}');
        alert('Waktu ujian telah habis!');
        document.getElementById('finishForm').submit();
      }
    }

    var timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    // Fungsi untuk menyimpan jawaban
    function saveAnswer() {
      const selectedAnswer = document.querySelector('input[name="answer"]:checked');
      const answerValue = selectedAnswer ? selectedAnswer.value : null;
      const markDoubtCheckbox = document.getElementById('markDoubtCheckbox');
      const markDoubtValue = markDoubtCheckbox.checked ? 1 : 0;

      console.log('Mengirim data:', {
        answer: answerValue,
        mark_doubt: markDoubtValue
      });

      return fetch("{{ route('student.exams.answer', [$exam->id, $currentQuestion->id]) }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          body: JSON.stringify({
            answer: answerValue,
            mark_doubt: markDoubtValue
          })
        })
        .then(res => res.json())
        .then(data => {
          console.log('Response dari server:', data);
          if (data.success) {
            console.log('Jawaban tersimpan. marked_doubt:', data.marked_doubt);
            return true;
          } else {
            alert('Gagal menyimpan jawaban: ' + (data.message || 'Terjadi kesalahan'));
            return false;
          }
        })
        .catch(err => {
          console.error('Error:', err);
          alert('Terjadi kesalahan saat menyimpan jawaban');
          return false;
        });
    }

    // Event listener untuk checkbox ragu-ragu
    document.getElementById('markDoubtCheckbox').addEventListener('change', function() {
      const checkbox = this;
      const originalState = checkbox.checked;

      // Tampilkan loading state sementara
      checkbox.disabled = true;

      console.log('Checkbox ragu-ragu diubah:', originalState);

      saveAnswer().then(success => {
        checkbox.disabled = false;

        if (!success) {
          // Jika gagal, kembalikan ke state semula
          checkbox.checked = !originalState;
        } else {
          // Jika berhasil, refresh untuk update navigasi
          window.location.reload();
        }
      });
    });

    // Event listener untuk semua tombol navigasi soal
    document.querySelectorAll('.question-nav').forEach(button => {
      button.addEventListener('click', function() {
        const questionId = this.getAttribute('data-question-id');
        const isCurrent = this.getAttribute('data-is-current') === 'true';

        // Jika soal yang diklik adalah soal saat ini, tidak perlu melakukan apa-apa
        if (isCurrent) {
          return;
        }

        saveAnswer().then(success => {
          if (success) {
            window.location.href = "{{ route('student.exams.do', [$exam->id, '']) }}/" + questionId;
          }
        });
      });
    });

    // Event listener untuk tombol sebelumnya
    if (document.getElementById('prevQuestion')) {
      document.getElementById('prevQuestion').addEventListener('click', () => {
        saveAnswer().then(success => {
          if (success) {
            window.location.href = "{{ route('student.exams.do', [$exam->id, $prevQuestionId]) }}";
          }
        });
      });
    }

    // Event listener untuk tombol selanjutnya
    if (document.getElementById('nextQuestion')) {
      document.getElementById('nextQuestion').addEventListener('click', () => {
        saveAnswer().then(success => {
          if (success) {
            window.location.href = "{{ route('student.exams.do', [$exam->id, $nextQuestionId]) }}";
          }
        });
      });
    }

    // Event listener untuk tombol selesaikan ujian
    if (document.getElementById('finishExam')) {
      document.getElementById('finishExam').addEventListener('click', () => {
        saveAnswer().then(success => {
          if (success) {
            if (confirm('Apakah Anda yakin ingin menyelesaikan ujian?')) {
              document.getElementById('finishForm').submit();
            }
          }
        });
      });
    }

    // Simpan jawaban otomatis saat memilih opsi
    const radioInputs = document.querySelectorAll('input[name="answer"]');
    radioInputs.forEach(input => {
      input.addEventListener('change', () => {
        saveAnswer().then(success => {
          if (success) {
            window.location.reload();
          }
        });
      });
    });

    // Prevent form submission on enter key
    document.getElementById('answerForm').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
      }
    });
  });
</script>
@endsection