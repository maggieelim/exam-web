@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Card Detail Exam -->
    <div class="card mb-4 p-3">
      <div class="d-flex justify-content-between align-items-center">
        <h5>{{ $exam->title }}</h5>
        <!-- Form Delete Exam (terpisah) -->
        <form action="{{ route('exams.destroy', $exam->exam_code) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus exam ini?')" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-danger">Delete Exam</button>
        </form>
      </div>
      <div class="row">
        <div class="col-md-4">
          <p><strong>Course:</strong> {{ $exam->course->name }}</p>
        </div>
        <div class="col-md-4">
          <p><strong>Exam Type:</strong> {{ $exam->examType->name }}</p>
        </div>
        <div class="col-md-4">
          <p><strong>Password:</strong> {{ $exam->password }}</p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <p><strong>Date:</strong> {{ $exam->exam_date->format('d-m-Y H:i') }}</p>
        </div>
        <div class="col-md-4">
          <p><strong>Duration:</strong> {{ $exam->duration }} minutes</p>
        </div>
      </div>
    </div>

    <!-- Card List Soal -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Daftar Soal</h4>
      <button class="btn btn-outline-secondary btn-sm " type="button"
        data-bs-toggle="collapse" data-bs-target="#filterCollapse"
        aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
      </button>
    </div>

    <div class="collapse" id="filterCollapse">
      <form method="GET" action="{{ route('exams.show', $exam->exam_code) }}">
        <div class="mx-3 my-2 py-2">
          <div class="row g-2">
            <div class="col-md-12">
              <label for="title" class="form-label mb-1">Soal</label>
              <input type="text" name="search" class="form-control" placeholder="Cari Soal Ujian"
                value="{{ request('search') }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
              <a href="{{ route('exams.show', $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
              <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div>
      @if($questions->count() > 0)
      @foreach($questions as $index => $question)
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <!-- Nomor soal & judul -->
          <p class="fw-bold mb-1">{{ $index + 1 }}. {{ $question->badan_soal }}</p>
          <!-- Kalimat tanya -->
          <p class="mb-2">{{ $question->kalimat_tanya }}</p>


          <!-- Pilihan jawaban dalam 2 kolom -->
          @if($question->options->count() > 0)
          <div class="row mt-2">
            @foreach($question->options as $option)
            <div class="col-6 mb-2">
              <span class="fw-bold">{{ $option->option }}.</span>
              <span>
                {{ $option->text }}
              </span>
              @if($option->is_correct)
              <span class="ms-1 text-success">✔</span>
              @endif
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>
      @endforeach
      @else
      <p class="text-muted">Belum ada soal untuk exam ini.</p>
      @endif
    </div>
    <div class="d-flex justify-content-center mt-3">
      <x-pagination :paginator="$questions" />
    </div>
  </div>

</div>
@endsection