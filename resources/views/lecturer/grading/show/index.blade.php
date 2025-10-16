@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid">

  <!-- Exam Header Section -->
  @include('lecturer.grading.show.partials.exam-header')

  <!-- Statistics Section -->
  @include('lecturer.grading.show.partials.statistics')

  <!-- Navigation Tabs -->
  @include('lecturer.grading.show.partials.navigation-tabs')

  <!-- Tab Content -->
  <div class="tab-content" id="examTabsContent">

    <!-- Results Tab -->
    @if($activeTab === 'results')
    <div class="tab-pane fade show active" id="results">
      @include('lecturer.grading.show.results')
    </div>
    @endif

    <!-- Answers Tab -->
    @if($activeTab === 'answers')
    <div class="tab-pane fade show active" id="answers">
      @include('lecturer.grading.show.answers')
    </div>
    @endif

  </div>

</div>

<!-- Styles -->
@include('lecturer.grading.show.partials.styles')

<!-- Scripts -->
@include('lecturer.grading.show.partials.scripts')

@endsection