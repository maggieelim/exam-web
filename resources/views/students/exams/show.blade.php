@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 card mb-3 p-3">
  <div class="d-flex justify-content-between align-items-center gap-2">
    <h5 class="text-uppercase text-dark font-weight-bolder">{{ $exam->title }} Blok {{ $exam->course->name }}</h5>
    <a href="{{ route('student.studentExams.index', 'previous') }}" class="btn btn-sm btn-secondary">
      Back
    </a>
  </div>
  <div class="row">
    <div class="col-md-6">
      <p><strong class="text-uppercase text-sm">Name:</strong> {{ $student->user->name }}</p>
    </div>
    <div class="col-md-6">
      <p><strong class="text-uppercase text-sm">NIM:</strong> {{ $student->nim }}</p>
    </div>
    <div class="col-md-6">
      <p><strong class="text-uppercase text-sm">Exam Date:</strong> {{ $exam->exam_date->format('d-m-Y') }}</p>
    </div>
    <div class="col-md-6">
      <p><strong class="text-uppercase text-sm">Total Questions:</strong> {{ $totalQuestions }}</p>
    </div>
    <div class="col-md-12">
      <p><strong class="text-uppercase text-sm">Feedback From Lecturer:</strong>
        <br>{{ $attempt->feedback ?: '-' }}
      </p>
    </div>
  </div>
</div>

@php
$count = count($exam->categories_result);
// Tentukan kolom dinamis berdasarkan jumlah kategori
$colClass = match(true) {
$count <= 2=> 'col-md-6 col-sm-6',
  $count == 3 => 'col-md-4 col-sm-6',
  $count == 4 => 'col-md-6 col-sm-6',
  $count == 5 => 'col-md-4 col-sm-6',
  default => 'col-md-12 col-sm-6',
  };
  @endphp
  <div class="row">
    @foreach($exam->categories_result as $cat)
    <div class="{{ $colClass }} mb-3">
      <div class="card shadow-sm border-0 p-3 text-center">
        <h6 class="text-uppercase fw-bold text-dark">{{ $cat['category_name'] }}</h6>
        <div class="d-flex align-items-center justify-content-center">
          <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
            <div class="progress-bar m-0
                                            @if($cat['percentage'] == 0) bg-secondary opacity-50
                                            @elseif($cat['percentage'] >= 80) bg-success
                                            @elseif($cat['percentage'] >= 60) bg-info
                                            @elseif($cat['percentage'] >= 40) bg-warning
                                            @else bg-danger
                                            @endif"
              role="progressbar"
              style="width: {{ max($cat['percentage'], 1) }}%"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="{{ $cat['percentage'] }}% - {{ $cat['total_correct'] }}/{{ $cat['total_question'] }} correct">
            </div>
          </div>
          <small class="text-muted">{{ $cat['percentage'] }}%</small>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <!-- Filter Section -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <small class="text-muted">Active Filter:</small>
      @if(request('answer_status') || request('feedback_status'))
      @if(request('answer_status') && request('answer_status') != 'all')
      <span class="badge bg-primary me-1">
        Answer:
        @if(request('answer_status') == 'correct') Correct
        @elseif(request('answer_status') == 'incorrect') Incorrect
        @elseif(request('answer_status') == 'not_answered') Not Answered
        @endif
      </span>
      @endif
      @if(request('feedback_status') && request('feedback_status') != 'all')
      <span class="badge bg-info">
        Feedback:
        @if(request('feedback_status') == 'with_feedback') With Feedback
        @elseif(request('feedback_status') == 'without_feedback') Without Feedback
        @endif
      </span>
      @endif
      @endif
    </div>
    <button class="btn btn-sm btn-outline-secondary" type="button"
      data-bs-toggle="collapse" data-bs-target="#filterCollapse"
      aria-expanded="false" aria-controls="filterCollapse">
      <i class="fas fa-filter me-1"></i> Filter
    </button>
  </div>

  <div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('student.results.show', $exam->exam_code) }}">
      <div class="row g-3">
        <!-- Filter Answer Status -->
        <div class="col-md-6">
          <label for="answer_status" class="form-label fw-bold">Answer Status</label>
          <select name="answer_status" id="answer_status" class="form-select">
            <option value="all" {{ request('answer_status') == 'all' || !request('answer_status') ? 'selected' : '' }}>All Answers</option>
            <option value="correct" {{ request('answer_status') == 'correct' ? 'selected' : '' }}>Correct</option>
            <option value="incorrect" {{ request('answer_status') == 'incorrect' ? 'selected' : '' }}>Incorrect</option>
            <option value="not_answered" {{ request('answer_status') == 'not_answered' ? 'selected' : '' }}>Not Answered</option>
          </select>
        </div>

        <!-- Filter Feedback -->
        <div class="col-md-6">
          <label for="feedback_status" class="form-label fw-bold">Feedback Status</label>
          <select name="feedback_status" id="feedback_status" class="form-select">
            <option value="all" {{ request('feedback_status') == 'all' || !request('feedback_status') ? 'selected' : '' }}></option>
            <option value="with_feedback" {{ request('feedback_status') == 'with_feedback' ? 'selected' : '' }}>With Feedback</option>
            <option value="without_feedback" {{ request('feedback_status') == 'without_feedback' ? 'selected' : '' }}>Without Feedback</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
          <a href="{{ route('student.results.show', $exam->exam_code) }}" class="btn btn-light btn-sm">
            <i class="fas fa-undo me-1"></i>Reset
          </a>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-check me-1"></i>Apply Filter
          </button>
        </div>
      </div>
    </form>
  </div>
  @forelse($paginatedQuestions as $question)
  <div class="card mb-3 shadow-sm 
    @if(!empty($question['student_feedback'])) border-start border-4 border-info @endif">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start">
        <div class="fw-bold">
          {{ $question['number'] }}. {{ $question['body'] }}
        </div>

        <div class="d-flex align-items-center gap-2">
          @if(!empty($question['student_feedback']))
          <span class="badge bg-info text-white">Feedback</span>
          @endif

          @if($question['is_answered'])
          <span class="badge {{ $question['is_correct'] ? 'bg-success' : 'bg-danger' }}">
            {{ $question['is_correct'] ? 'Correct' : 'Incorrect' }}
          </span>
          @else
          <span class="badge bg-secondary">Not Answered</span>
          @endif
          <small class="text-muted"> {{ $question['category'] }} </small>
        </div>
      </div>

      <p class="mt-2 mb-2 text-muted">{{ $question['question_text'] }}</p>

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
        <label class="fw-bold d-block mb-1">Feedback for this question:</label>
        <p class="mb-0 {{ !empty($question['student_feedback']) ? 'text-dark' : 'text-muted' }}">
          {{ $question['student_feedback'] ?? 'No Feedback' }}
        </p>
      </div>
    </div>
  </div>
  @empty
  <div class="card">
    <div class="card-body text-center py-5">
      <i class="fas fa-search fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">Tidak ada soal yang sesuai dengan filter</h5>
      <p class="text-muted">Coba ubah filter atau lihat semua soal</p>
      <a href="{{ route('student.results.show', $exam->exam_code) }}" class="btn btn-primary">
        Lihat Semua Soal
      </a>
    </div>
  </div>
  @endforelse

  <!-- Pagination -->
  @if($paginatedQuestions->hasPages())
  <div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$paginatedQuestions" />
  </div>
  @endif
  @endsection

  @push('css')
  <style>
    .form-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
  </style>
  @endpush