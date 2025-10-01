@extends('layouts.user_type.auth')

@section('content')
<!-- Exam Info -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h4>{{ $exam->title }}</h4>
        <p class="mb-0">Course: {{ $exam->course->name }} | Total Questions: {{ $exam->questions_count }} | Total Students: {{ $exam->attempts_count }}</p>
      </div>
      <div>
        @if(!$exam->is_published)
        <span class="badge bg-danger">Unpublished</span>
        @else
        <span class="badge bg-success">Published</span>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" id="examTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link {{ $activeTab === 'results' ? 'active' : '' }}"
      href="{{ request()->fullUrlWithQuery(['tab' => 'results']) }}">
      Results
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link {{ $activeTab === 'analytics' ? 'active' : '' }}"
      href="{{ request()->fullUrlWithQuery(['tab' => 'analytics']) }}">
      Analytics
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link {{ $activeTab === 'ranking' ? 'active' : '' }}"
      href="{{ request()->fullUrlWithQuery(['tab' => 'ranking']) }}">
      Ranking
    </a>
  </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="examTabsContent">
  <!-- RESULTS TAB -->
  <div class="tab-pane fade {{ $activeTab === 'results' ? 'show active' : '' }}" id="results">
    @include('lecturer.grading.show.results')
  </div>

  <div class="tab-pane fade {{ $activeTab === 'analytics' ? 'show active' : '' }}" id="analytics">
    @include('lecturer.grading.show.analytics')
  </div>

  <div class="tab-pane fade {{ $activeTab === 'ranking' ? 'show active' : '' }}" id="ranking">
    @include('lecturer.grading.show.ranking')
  </div>

</div>

<style>
  .nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    padding: 12px 24px;
  }

  .nav-tabs .nav-link.active {
    font-weight: 600;
    border-bottom: 3px solid #007bff;
    background: transparent;
  }

  .nav-tabs {
    border-bottom: 1px solid #dee2e6;
  }
</style>
@endsection