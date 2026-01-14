@php
$stats = [
[
'value' => $analytics['total_students'] ?? 0,
'label' => 'Total Participant',
'color' => 'primary',
'icon' => 'fas fa-users',
],
[
'value' => number_format($analytics['average_score'] ?? 0, 1) . '%',
'label' => 'Average Score',
'color' => 'success',
'icon' => 'fas fa-chart-line',
],
[
'value' => number_format($analytics['highest_score'] ?? 0, 1) . '%',
'label' => 'Highest Score',
'color' => 'info',
'icon' => 'fas fa-trophy',
],
[
'value' => $analytics['total_question'] ?? 0,
'label' => 'Total Questions',
'color' => 'warning',
'icon' => 'fas fa-file',
],
];
@endphp

@foreach ($stats as $stat)
<div class="col-md-3 col-6 ">
    <div class="text-center pt-3">
        <i class="{{ $stat['icon'] }} text-{{ $stat['color'] }} fa-2x mb-2"></i>
        <h3 class="text-{{ $stat['color'] }} mb-1">{{ $stat['value'] }}</h3>
        <p class="text-muted mb-0 small">{{ $stat['label'] }}</p>
    </div>
</div>
@endforeach