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
            <p><strong class="text-uppercase text-sm">Exam Date:</strong> {{ $exam->formatted_date }}</p>
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
$colClass = match (true) {
$count <= 2=> 'col-md-6 col-sm-6',
    $count == 3 => 'col-md-4 col-sm-6',
    $count == 4 => 'col-md-6 col-sm-6',
    $count == 5 => 'col-md-4 col-sm-6',
    default => 'col-md-12 col-sm-6',
    };
    @endphp
    <div class="row">
        @foreach ($exam->categories_result as $cat)
        <div class="{{ $colClass }} mb-3">
            <div class="card shadow-sm border-0 p-3 text-center">
                <h6 class="text-uppercase fw-bold text-dark">{{ $cat['category_name'] }}</h6>
                <div class="d-flex align-items-center justify-content-center">
                    <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
                        <div class="progress-bar m-0
                                            @if ($cat['percentage'] == 0) bg-secondary opacity-50
                                            @elseif($cat['percentage'] >= 80) bg-success
                                            @elseif($cat['percentage'] >= 60) bg-info
                                            @elseif($cat['percentage'] >= 40) bg-warning
                                            @else bg-danger @endif" role="progressbar"
                            style="width: {{ max($cat['percentage'], 1) }}%" data-bs-toggle="tooltip"
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


    @endsection

    @push('css')
    <style>
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
    @endpush