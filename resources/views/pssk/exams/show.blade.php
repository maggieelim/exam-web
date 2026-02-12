@extends('layouts.user_type.auth')

@section('content')
<div class="col-12">
  <!-- Card Detail Exam -->
  <div class="card mb-3 p-3">
    <div class="d-flex justify-content-between align-items-center">
      <h5>{{ $exam->title }}</h5>
      <!-- Form Delete Exam (terpisah) -->
      @if($exam->status === 'upcoming')
      <form action="{{ route('exams.destroy', $exam->exam_code) }}" method="POST"
        onsubmit="return confirm('Yakin ingin menghapus exam ini?')" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">Delete Exam</button>
      </form>
      @endif
    </div>
    <div class="row">
      <div class="col-md-4">
        <p><strong>Course:</strong> {{ $exam->course->name }}</p>
      </div>
      <div class="col-md-4">
        <p><strong>Semester:</strong> {{ $exam->semester->semester_name }} {{ $exam->semester->academicYear->year_name
          }}</p>
      </div>
      <div class="col-md-4">
        <p><strong>Date:</strong> {{ $exam->exam_date?->format('d-m-Y') ?? '-' }}</p>
      </div>
      <div class="col-md-4">
        <p><strong>Duration:</strong> {{ $exam->duration }} minutes</p>
      </div>
      <div class="col-md-4">
        <p><strong>Total Participants:</strong> {{ $total_participants }}</p>
      </div>
      <div class="col-md-4">
        <p><strong>Password:</strong> {{ $exam->password }}</p>
      </div>
    </div>
  </div>

  <!-- Card List Soal -->
  <div class="d-flex justify-content-between align-items-center">
    <h4>Daftar Soal</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('exams.questions.download', $exam->exam_code) }}"
        class="btn py-md-2 bg-gradient-success d-flex align-items-center">
        <i class="fas fa-download"></i>
        <span class="d-none d-md-inline ms-1">Question</span>
      </a>
      <a href="{{ route('exams.questions.' . $status, $exam->exam_code) }}"
        class="btn py-md-2 bg-gradient-warning d-flex align-items-center">
        <i class="fas fa-edit"></i>
        <span class="d-none d-md-inline ms-1">Edit Question</span>
      </a>
      <button class="btn py-md-2 btn-outline-secondary d-flex align-items-center" type="button"
        data-bs-toggle="collapse" data-bs-target="#filterCollapse">
        <i class="fas fa-filter"></i>
        <span class="d-none d-md-inline ms-1">Filter</span>
      </button>
    </div>
  </div>

  <div class="collapse card mb-3" id="filterCollapse">
    <form method="GET" action="{{ route('exams.show.'.$status, $exam->exam_code) }}">
      <div class="mx-3 my-2 py-2">
        <div class="row g-2">
          <div class="col-md-12">
            <label for="title" class="form-label mb-1">Soal</label>
            <input type="text" name="search" class="form-control" placeholder="Cari Soal Ujian"
              value="{{ request('search') }}">
          </div>

          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('exams.show.'.$status, $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div>
    @forelse ($questions as $index => $question)
    <div class="card p-3 mb-3 shadow-sm">
      <div class="d-flex flex-column-reverse flex-md-row justify-content-between">

        <div class="d-flex gap-2 w-md-90">
          <p class="mb-1">{{ $index + 1 }}.</p>
          <div>
            <p class="my-0">
              {!! nl2br(e(trim($question->badan_soal ))) !!}
            </p>
            @if ($question->image)
            <img src="{{ asset('storage/' . $question->image) }}" alt="Gambar Soal"
              class="mx-3 my-1 img-fluid rounded shadow-sm" style="max-width: 150px;">
            @endif
            <p class="my-0">
              {!! nl2br(e(trim($question->kalimat_tanya))) !!}
            </p>
            <small class="fw-bold d-block mt-2">Jawaban:</small>
            <div class="row">
              @foreach ($question->options as $option)
              <div class="col-12 mb-2">
                <span class="fw-bold">{{ $option->option }}.</span>
                <span>{{ $option->text }}</span>

                @if ($option->is_correct)
                <span class="ms-1 text-success">✔</span>
                @endif

                @if ($option->image)
                <div class="ps-3 mt-1">
                  <img src="{{ asset('storage/' . $option->image) }}" alt="Gambar Opsi"
                    class="img-fluid rounded shadow-sm" style="max-width: 150px;">
                </div>
                @endif
              </div>
              @endforeach
            </div>
          </div>
        </div>

        <div>
          <small class="text-muted mb-1 mb-md-0">
            Kategori: {{ $question->category->name ?? 'Tidak ada kategori' }}
          </small>
        </div>
      </div>
    </div>

    @empty
    <div class="card">
      <div class="card-body">
        <p class="text-muted mb-0">Belum ada soal untuk exam ini.</p>
      </div>
    </div>
    @endforelse
  </div>

  <div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$questions" />
  </div>
</div>
@endsection