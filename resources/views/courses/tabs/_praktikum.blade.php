<div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
    </button>
    <a class="btn bg-gradient-primary btn-sm"
        href="{{ route('admin.courses.addLecturer', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
        Pilih Dosen
    </a>
</div>


<form class="schedule-form" action="{{ route('admin.course.assignPracticum') }}" method="POST">
    @csrf
    <div class="table-responsive p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Dosen</th>
                    <th rowspan="2">Bagian</th>
                    @foreach ($practicumData->practicums as $practicum)
                        <th>{{ $practicum->topic }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($practicumData->practicums as $practicum)
                        <th>{{ $practicum->group }}<br>{{ $practicum->scheduled_date }}<br>{{ $practicum->start_time }}<br>{{ $practicum->end_time }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($practicumData->lecturers as $index => $lecturer)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $lecturer->lecturer->user->name }}</td>
                        <td>{{ $lecturer->lecturer->bagian }}</td>
                        @foreach ($practicumData->practicums as $practicum)
                            @php
                                $isUnavailable = in_array(
                                    $practicum->id,
                                    $practicumData->unavailableSlots[$lecturer->lecturer_id] ?? [],
                                );
                                $isAssigned = $practicum->practicumDetails->contains(
                                    'lecturer_id',
                                    $lecturer->lecturer_id,
                                );
                                $isDisabled = $isUnavailable && !$isAssigned;
                            @endphp

                            @if ($isDisabled && !$isAssigned)
                                <td class="text-center soft-info1">
                                    <input disabled type="checkbox" class="group-checkbox input-bg"
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $practicum->id }}]"
                                        value="1"
                                        {{ $practicum->practicumDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
                                </td>
                            @else
                                <td class="text-center">
                                    <input type="checkbox" class="group-checkbox input-bg"
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $practicum->id }}]"
                                        value="1"
                                        {{ $practicum->practicumDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
    </div>
</form>
