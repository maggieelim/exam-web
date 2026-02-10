<div class="d-flex gap-2">
    <a class="btn btn-outline-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
        href="{{ route('admin.courses.downloadPleno', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
        title="Download Excel">
        <i class="fas fa-download"></i>
    </a>
</div>

<form class="schedule-form" action="{{ route('admin.course.assignPleno') }}" method="POST">
    @csrf
    <div class="table-wrapper p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th class="headcol view" rowspan="2">#</th>
                    <th class="headcol no headrow">No</th>
                    <th class="headcol name">Nama Dosen</th>
                    <th class="headcol bagian">Bagian</th>
                    @foreach ($plenoData->plenos as $pleno)
                    <th class="headrow text-wrap">P{{ $pleno->session_number }}
                        <br>
                        {{ $pleno->scheduled_date }}
                        <br>
                        {{ $pleno->start_time }}
                        <br>
                        {{ $pleno->end_time }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($plenoData->lecturers as $index => $lecturer)
                <tr>
                    <td class="text-center headcol view">
                        <a href="{{ route('schedules.index', ['lecturer_id' => $lecturer->lecturer->id]) }}"
                            class="text-info text-decoration-underline" title="Lihat Jadwal"> View
                        </a>
                    </td>
                    <td class="text-center headcol no">{{ $index + 1 }}</td>
                    <td class="headcol name">{{ $lecturer->lecturer->user->name }}</td>
                    <td class="headcol bagian">{{ $lecturer->lecturer->bagian }}</td>
                    @foreach ($plenoData->plenos as $pleno)
                    @php
                    $isUnavailable = in_array(
                    $pleno->id,
                    $plenoData->unavailableSlots[$lecturer->lecturer_id] ?? [],
                    );
                    $isAssigned = $pleno->plenoDetails->contains('lecturer_id', $lecturer->lecturer_id);
                    $isDisabled = $isUnavailable && !$isAssigned;
                    @endphp

                    @if ($isDisabled && !$isAssigned)
                    <td class="text-center soft-info1">
                        <input disabled type="checkbox" class="group-checkbox input-bg"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $pleno->id }}]" value="1" {{
                            $pleno->plenoDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
                    </td>
                    @else
                    <td class="text-center clickable-td">
                        <input type="checkbox" class="group-checkbox input-bg"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $pleno->id }}]" value="1" {{
                            $pleno->plenoDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
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