 <a href="{{ route('admin.course.create', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
     class="btn bg-gradient-secondary my-3 p-2 px-3" title="Info">
     Bentuk Kelas
 </a>

 @foreach ($teachingSchedules as $activityName => $schedules)
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
