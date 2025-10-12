@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 card mb-4 p-3">
  <div class="d-flex justify-content-between align-items-center gap-2">
    <h5>{{ $exam->title }}</h5>
    <a href="{{ route('lecturer.grade.'.$status, [
    'exam_code' => $exam->exam_code
]) }}" class="btn btn-sm btn-secondary">
      Back
    </a>
  </div>
  <div class="row">
    <div class="col-md-4">
      <p><strong>Name:</strong> {{ $student->user->name }}</p>
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
      <p><strong>Duration:</strong> {{ $exam->duration }} minutes</p>
    </div>
  </div>
</div>

<!-- Daftar Soal -->
<div class="d-flex justify-content-between align-items-center">
  <h4 class="mb-3">Daftar Soal</h4>
  <button class="btn btn-sm btn-outline-secondary" type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filterCollapse">
    <i class="fas fa-filter me-1"></i> Filter
  </button>
</div>

<div class="collapse" id="filterCollapse">
  <form method="GET" action="{{ route('lecturer.feedback.'.$status, [
    'exam_code' => $exam->exam_code,
    'nim'       => $student->nim
]) }}">
    <div class="card card-body">
      <div class="row g-2">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="col-md-12">
          <label for="answer_status" class="form-label mb-1">Answer Status</label>
          <select name="answer_status" id="answer_status" class="form-control">
            <option value="">-- All Answers --</option>
            <option value="correct" {{ request('answer_status') == 'correct' ? 'selected' : '' }}>Correct</option>
            <option value="incorrect" {{ request('answer_status') == 'incorrect' ? 'selected' : '' }}>Incorrect</option>
            <option value="not_answered" {{ request('answer_status') == 'not_answered' ? 'selected' : '' }}>Not Answered</option>
          </select>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
          <a href="{{ route('lecturer.feedback.'.$status, [
    'exam_code' => $exam->exam_code,
    'nim'       => $student->nim
]) }}" class="btn btn-light btn-sm">Reset</a>
          <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        </div>
      </div>
    </div>
  </form>
</div>

<form action="{{ route('lecturer.feedback.update', [
    'exam_code' => $exam->exam_code,
    'nim'       => $student->nim
]) }}" method="POST">
  @csrf
  @method('PUT')

  @forelse($paginatedQuestions as $question)
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <p class="fw-bold mb-0">{{ $question['number'] }}. {{ $question['body'] }}</p>
        <div class="d-flex gap-2">
          @if($question['is_answered'])
          <span class="badge {{ $question['is_correct'] ? 'bg-success' : 'bg-danger' }} align-self-start">
            {{ $question['is_correct'] ? 'Correct' : 'Incorrect' }}
          </span>
          @else
          <span class="badge bg-secondary align-self-start">Not Answered</span>
          @endif
          <small class="text-muted">
            {{ $question['category'] }}
          </small>
        </div>
      </div>

      <p class="mb-2">{{ $question['question_text'] }}</p>

      @if($question['image'])
      <div class="my-3">
        <img src="{{ asset('storage/' . $question['image']) }}"
          alt="Gambar Soal"
          class="img-fluid rounded shadow-sm"
          style="max-width: 400px;">
      </div>
      @endif

      <!-- Pilihan Jawaban -->
      @if(count($question['options']) > 0)
      <div class="row mt-2">
        @foreach($question['options'] as $option)
        <div class="col-6 mb-2">
          <span class="fw-bold">{{ $option['option'] }}.</span>
          <span>{{ $option['text'] }}</span>
          @if($option['is_correct'])
          <span class="text-success fw-bold">✓</span>
          @elseif($option['is_student_answer'])
          <span class="text-danger fw-bold">✗</span>
          @endif
        </div>
        @endforeach
      </div>
      @endif

      <!-- Feedback per soal -->
      <div class="mt-3">
        <label class="form-label fw-bold">Feedback untuk soal ini:</label>
        <input type="text" name="feedback[{{ $question['id'] }}]" class="form-control"
          value="{{ old("feedback.{$question['id']}", $question['student_feedback']) }}"
          placeholder="Tulis feedback dosen untuk soal ini...">
      </div>
    </div>
  </div>
  @empty
  <p class="text-muted">Belum ada soal untuk exam ini.</p>
  @endforelse

  <!-- Pagination -->
  @if($paginatedQuestions->hasPages())
  <div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$paginatedQuestions" />
  </div>
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