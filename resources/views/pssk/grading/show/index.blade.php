@extends('layouts.user_type.auth')

@section('content')

<!-- Exam Header Section -->
@include('pssk.grading.show.partials.exam-header')

<!-- Statistics Section -->
@include('pssk.grading.show.partials.statistics')

<!-- Navigation Tabs -->
@include('pssk.grading.show.partials.navigation-tabs')

<!-- Tab Content -->
<div class="tab-content" id="examTabsContent">

  <!-- Results Tab -->
  @if($activeTab === 'results')
  <div class="tab-pane fade show active" id="results">
    @include('pssk.grading.show.results')
  </div>
  @endif

  <!-- Answers Tab -->
  @if($activeTab === 'answers')
  <div class="tab-pane fade show active" id="answers">
    @include('pssk.grading.show.answers')
  </div>
  @endif

</div>


<!-- Styles -->
@include('pssk.grading.show.partials.styles')

<!-- Scripts -->
@include('pssk.grading.show.partials.scripts')

@endsection