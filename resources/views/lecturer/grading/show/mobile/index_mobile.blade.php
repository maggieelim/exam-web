@extends('layouts.user_type.auth')

@section('content')
    <div class="card mb-4 p-3">
        <h5 class="card-title mb-2">
            <i class="fas fa-chart-bar me-2"></i>
            General Exam Statistics ({{ $exam->title }} Blok {{ $exam->course->name }})
            <span
                class="badge align-items-center justify-content-center {{ $exam->is_published ? 'bg-success' : 'bg-danger' }} ms-2"
                style="font-size: 0.8rem;">
                {{ $exam->is_published ? 'Published' : 'Unpublished' }}
            </span>
        </h5>
        <div class="d-flex justify-content-end gap-2 mb-0 pb-0">
            <button class="btn d-flex align-items-center justify-content-center btn-outline-primary" type="button"
                data-bs-toggle="collapse" style="height: 32px;" data-bs-target="#chartCollapse" aria-expanded="false"
                aria-controls="chartCollapse">
                <i class="fas fa-filter me-1"></i> Charts
            </button>

            <a href="{{ route('lecturer.results.index', $status) }}" class="btn btn-sm btn-outline-secondary">
                Back
            </a>
        </div>
    </div>

    <!-- Statistics Section -->
    @include('lecturer.grading.show.partials.statistics')

    <!-- Navigation Tabs -->
    @include('lecturer.grading.show.partials.navigation-tabs')

    <!-- Tab Content -->
    <div class="tab-content" id="examTabsContent">

        <!-- Results Tab -->
        @if ($activeTab === 'results')
            <div class="tab-pane fade show active" id="results">
                @include('lecturer.grading.show.mobile.results_mobile')
            </div>
        @endif

        <!-- Answers Tab -->
        @if ($activeTab === 'answers')
            <div class="tab-pane fade show active" id="answers">
                @include('lecturer.grading.show.mobile.answers_mobile')
            </div>
        @endif

    </div>


    <!-- Styles -->
    @include('lecturer.grading.show.partials.styles')

    <!-- Scripts -->
    @include('lecturer.grading.show.partials.scripts')
@endsection
