<div class="d-flex">
    <a class="btn btn-sm btn-success"
        href="{{ route('admin.courses.downloadPracticumAssignment', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
        title="Download Excel">
        <i class="fas fa-download"></i> Download Excel
    </a>
</div>

<form class="schedule-form"    action="{{ route('admin.course.assignPracticum') }}" method="POST">
    @csrf
    <div class="table-wrapper p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="table table-bordered compact-table m-0">
            <thead class="text-center align-middle">
                <tr>
                    <th rowspan="2" class="headcol no">No</th>
                    <th rowspan="2" class="headcol name">Nama Dosen</th>
                    <th rowspan="2" class="headcol bagian">Bagian</th>
                    @foreach ($practicumData->practicums as $practicum)
                    <th>{{ $practicum->topic }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($practicumData->practicums as $practicum)
                    <th>{{ $practicum->group }}<br>{{ $practicum->scheduled_date }}<br>{{ $practicum->start_time
                        }}<br>{{ $practicum->end_time }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($practicumData->lecturers as $index => $lecturer)
                <tr>
                    <td class="text-center headcol no">{{ $index + 1 }}</td>
                    <td class="headcol name">{{ $lecturer->lecturer->user->name }}</td>
                    <td class="headcol bagian">{{ $lecturer->lecturer->bagian }}</td>
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
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $practicum->id }}]" value="1" {{
                            $practicum->practicumDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' :
                        '' }}>
                    </td>
                    @else
                    <td class="text-center">
                        <input type="checkbox" class="group-checkbox input-bg"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $practicum->id }}]" value="1" {{
                            $practicum->practicumDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' :
                        '' }}>
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