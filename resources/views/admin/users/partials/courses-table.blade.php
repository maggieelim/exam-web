<div class="col-12 ">
  <h6 class="text-dark mb-3">Daftar Blok</h6>
  <div class="table-responsive">
    <table class="table align-items-center mb-0">
      <thead>
        <tr>
          <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Blok</th>
          <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Semester</th>
        </tr>
      </thead>
      <tbody>
        @foreach($courses as $course)
        @php
        $relation = $type === 'student' ? 'courseStudents' : 'courseLecturer';
        $latestSemester = $course->{$relation}->sortByDesc('semester.start_date')->first();
        @endphp
        <tr>
          <td class="align-middle text-center">{{ $course->name }}</td>
          <td class="align-middle text-center">
            {{ $latestSemester?->semester?->semester_name ?? '-' }}
            {{ $latestSemester?->semester?->academicYear?->year_name ?? '' }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>