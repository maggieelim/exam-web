<div class="d-flex gap-2">
    <a href="{{ route('admin.course.create', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
        class="btn btn-sm bg-gradient-secondary " title="Info">
        Bentuk Kelas
    </a>
    <a class="btn btn-outline-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
        href="{{ route('admin.courses.downloadPerkuliahan', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
        title="Download Excel">
        <i class="fas fa-download"></i>
    </a>
</div>
@foreach ($kelasData->teachingSchedules as $activityName => $schedules)
@if ($activityName === 'KULIAH')
@include('courses.kelas._kuliah', ['schedules' => $schedules])
@elseif ($activityName === 'PEMICU')
@include('courses.kelas._pemicu', ['schedules' => $schedules])
@elseif ($activityName === 'PRAKTIKUM')
@include('courses.kelas._praktikum', ['schedules' => $schedules])
@elseif ($activityName === 'SKILL LAB')
@include('courses.kelas._skilllab', ['schedules' => $schedules])
@elseif ($activityName === 'UJIAN TEORI')
@include('courses.kelas._ujian_teori', ['schedules' => $schedules])
@elseif ($activityName === 'PLENO')
@include('courses.kelas._pleno', ['schedules' => $schedules])
@endif
@endforeach