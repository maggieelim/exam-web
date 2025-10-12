@extends('layouts.user_type.auth')

@section('content')
<!-- Exam Info -->
<div class="row ">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="fas fa-chart-bar me-2"></i>General Exam Statistics ({{ $exam->title  }})
        </h5>
        <div class="d-flex gap-3">
          <div>
            @if(!$exam->is_published)
            <span class="badge bg-danger">Unpublished</span>
            @else
            <span class="badge bg-success">Published</span>
            @endif
          </div>
          <a href="{{ route('lecturer.results.index', $status) }}" class="btn btn-sm btn-outline-secondary">
            Back
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-primary">{{ $analytics['total_students'] ?? 0 }}</h3>
              <p class="text-muted mb-0">Total Participant</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-success">{{ number_format($analytics['average_score'] ?? 0, 1) }}%</h3>
              <p class="text-muted mb-0">Average Score</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-info">{{ number_format($analytics['highest_score'] ?? 0, 1) }}%</h3>
              <p class="text-muted mb-0">Highest Score</p>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="text-center">
              <h3 class="text-warning">{{ $analytics['total_question'] }}</h3>
              <p class="text-muted mb-0">Total Questions</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Tabs Navigation -->
<div class="nav-wrapper position-relative end-0 my-4">
  <ul class="nav nav-pills nav-fill p-1" id="examTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'results' ? 'active' : '' }}"
        href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=results">
        Exam Results
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'analytics' ? 'active' : '' }}"
        href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=analytics">
        Question Analytics
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'answers' ? 'active' : '' }}"
        href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=answers">
        Answer Distribution
      </a>
    </li>
  </ul>
</div>
<!-- Tab Content -->
<div class="tab-content" id="examTabsContent">
  <!-- RESULTS TAB -->
  <div class="tab-pane fade {{ $activeTab === 'results' ? 'show active' : '' }}" id="results">
    @include('lecturer.grading.show.results')
  </div>

  <div class="tab-pane fade {{ $activeTab === 'analytics' ? 'show active' : '' }}" id="analytics">
    @include('lecturer.grading.show.analytics')
  </div>

  <div class="tab-pane fade {{ $activeTab === 'answers' ? 'show active' : '' }}" id="answers">
    @include('lecturer.grading.show.answers')
  </div>
</div>

<style>
  .nav-pills .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    padding: 12px 24px;
  }

  .nav-pills .nav-link.active {
    font-weight: 600;
    border-bottom: 3px solid #007bff;
    background: white;
  }

  .nav-pills {
    border-bottom: 1px solid #dee2e6;
  }
</style>
@endsection