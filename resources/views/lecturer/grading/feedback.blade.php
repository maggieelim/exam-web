@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 card mb-4 p-3">
  <div class="d-flex justify-content-between align-items-center">
    <h5>{{ $exam->title }}</h5>
  </div>
  <div class="row">
    <div class="col-md-4">
      <p><strong>Name:</strong> {{ $student->name }}</p>
    </div>
    <div class="col-md-4">
      <p><strong>NIM:</strong> {{ $student->nim }}</p>
    </div>
    <div class="col-md-4">
      <p><strong>Angkatan:</strong> {{ $student->angkatan }}</p>
    </div>
    <div class="col-md-4">
      <p><strong>Date:</strong> {{ $exam->exam_date->format('d-m-Y') }}</p>
    </div>
    <div class="col-md-4">
      <p><strong>Score:</strong> {{ $exam->duration }} minutes</p>
    </div>
  </div>
</div>

<!-- Daftar Soal -->
<h4 class="mb-3">Daftar Soal</h4>

<form action="{{ route('lecturer.feedback.update', [
    'exam_code' => $exam->exam_code,
    'nim'       => $student->nim
]) }}" method="POST">
  @csrf
  @method('PUT')

  @if($exam->questions->count() > 0)
  @foreach($exam->questions as $index => $question)

  @php
  // Cari jawaban mahasiswa untuk soal ini
  $userAnswer = $userAnswers->firstWhere('exam_question_id', $question->id);
  $isAnswered = !is_null($userAnswer);
  $isCorrect = $userAnswer ? $userAnswer->is_correct : false;
  $studentAnswerId = $userAnswer ? $userAnswer->answer : null;
  $currentFeedback = $userAnswer ? $userAnswer->feedback : '';
  @endphp
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <p class="fw-bold mb-0">{{ $index + 1 }}. {{ $question->badan_soal }}</p>
        <div class="d-flex gap-2">
          @if($isAnswered)
          <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} align-self-start">
            {{ $isCorrect ? 'Benar' : 'Salah' }}
          </span>
          @else
          <span class="badge bg-secondary">Belum Dijawab</span>
          @endif
          <small class="text-muted">
            Kategori: {{ $question->category?->name ?? 'Tidak ada kategori' }}
          </small>
        </div>
      </div>

      <p class="mb-2">{{ $question->kalimat_tanya }}</p>

      @if($question->image)
      <div class="my-3">
        <img src="{{ asset('storage/' . $question->image) }}"
          alt="Gambar Soal"
          class="img-fluid rounded shadow-sm"
          style="max-width: 400px;">
      </div>
      @endif

      <!-- Pilihan Jawaban -->
      @if($question->options->count() > 0)
      <div class="row mt-2">
        @foreach($question->options as $option)
        @php
        $isStudentAnswer = $studentAnswerId == $option->id;
        $isCorrectOption = $option->is_correct;
        @endphp
        <div class="col-6 mb-2">
          <span class="fw-bold">{{ $option->option }}.</span>
          <span>{{ $option->text }}</span>
          @if($isCorrectOption)
          <span class="text-success fw-bold">✓</span>
          @elseif($isStudentAnswer)
          <span class="text-danger fw-bold">✗</span>
          @endif
        </div>
        @endforeach
      </div>
      @endif

      <!-- Feedback per soal -->
      <div class="mt-3">
        <label class="form-label fw-bold">Feedback untuk soal ini:</label>
        <input type="text" name="feedback[{{ $question->id }}]" class="form-control"
          value="{{ old("feedback.$question->id", $userAnswer ? $userAnswer->feedback : '') }}"
          placeholder="Tulis feedback dosen untuk soal ini...">
      </div>
    </div>
  </div>
  @endforeach
  @else
  <p class="text-muted">Belum ada soal untuk exam ini.</p>
  @endif


  <!-- Feedback keseluruhan (sticky di bawah) -->
  <div class="card position-sticky bottom-0 bg-white p-3 border-top shadow-sm mt-4">
    <div class="d-flex align-items-center gap-2">
      <input type="text" name="overall_feedback" class="form-control"
        placeholder="Tulis feedback untuk seluruh ujian..."
        value="{{ old('feedback', $attempt->feedback) }}">
      <button type="submit" class="btn btn-sm btn-primary">
        Simpan
      </button>
    </div>
  </div>
</form>


@endsection