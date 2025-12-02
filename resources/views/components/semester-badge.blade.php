@props(['semester', 'activeSemester'])

@if ($semester)
@php
$isActive = $activeSemester && $semester->id == $activeSemester->id;
$badgeClass = $isActive ? 'bg-gradient-success' : 'bg-gradient-info';
$label = $isActive ? 'Active' : 'Not Active';
@endphp

<span class="badge {{ $badgeClass }} text-white">
    {{ $semester->semester_name }} -
    {{ $semester->academicYear->year_name }}
    ({{ $label }})
</span>
@endif