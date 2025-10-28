@extends('layouts.user_type.auth')

@section('content')
    <div class="card mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5>{{ $exam->title }}</h5>
            <!-- Form Delete Exam (terpisah) -->
            @if ($exam->status === 'upcoming')
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
                <p><strong>Semester:</strong> {{ $exam->semester->semester_name }}
                    {{ $exam->semester->academicYear->year_name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Date:</strong> {{ $exam->exam_date->format('d-m-Y') }}</p>
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

    <div>
        <div>
            <h5 class="mb-2">Daftar Soal</h5>
        </div>
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('exams.questions.download', $exam->exam_code) }}" style="height: 32px;"
                class="btn bg-gradient-success d-flex align-items-center justify-content-center">
                <i class="fas fa-download"></i> Questions
            </a>
            <a href="{{ route('exams.questions.' . $status, $exam->exam_code) }}" style="height: 32px;"
                class="btn bg-gradient-warning d-flex align-items-center justify-content-center">
                <i class="fas fa-edit"></i> </a>
            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>

    <div class="collapse card mb-3" id="filterCollapse">
        <form method="GET" action="{{ route('exams.show.' . $status, $exam->exam_code) }}">
            <div class="mx-3 my-2 py-2">
                <div class="row g-2">
                    <div class="col-md-12">
                        <label for="title" class="form-label mb-1">Soal</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari Soal Ujian"
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('exams.show.' . $status, $exam->exam_code) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @foreach ($questions as $index => $question)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-auto question-card" id="question-{{ $question->id }}">
                <div class="card-body">
                    <div class="d-flex justify-content-end pb-0 ">
                        <small class="text-muted">
                            Category: {{ $question->category ? $question->category->name : 'Tidak ada kategori' }}
                        </small>
                    </div>
                    <p class="mb-1">
                        <span class="fw-bold">{{ $index + 1 }}. {{ $question->badan_soal }}</span>
                        <span>{{ $question->kalimat_tanya }}</span>
                    </p>
                    @if ($question->image)
                        <div class="my-3">
                            <img src="{{ asset('storage/' . $question->image) }}" alt="Soal Image" width="300"
                                class="border rounded">
                        </div>
                    @endif
                    <!-- Pilihan jawaban dalam 2 kolom -->
                    @if ($question->options->count() > 0)
                        <div class="row mt-2">
                            @foreach ($question->options as $option)
                                <div class="col-12 mb-2">
                                    <span class="fw-bold">{{ $option->option }}.</span>
                                    <span>
                                        {{ $option->text }}
                                    </span>
                                    @if ($option->is_correct)
                                        <span class="ms-1 text-success">✔</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$questions" />
    </div>
@endsection
@push('dashboard')
