<div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-info"
        href="{{ route('admin.courses.downloadSkillsLab', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
        title="Download Excel">
        <i class="fas fa-download"></i>
    </a>
</div>
<form class="schedule-form" action="{{ route('admin.course.assignSkillLab') }}" method="POST">
    @csrf
    <div class="table-wrapper p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th class="headcol no headrow" rowspan="2">No</th>
                    <th class="headcol name" rowspan="2">Nama Dosen</th>
                    @foreach ($skillLabData->skillsLabs as $index => $skillLab)
                    <th>{{ $skillLab->topic ?? 'Lab' . ' ' . $index + 1 }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($skillLabData->skillsLabs as $skillLab)
                    <th class="headrow">{{ $skillLab->group }}
                        {{ $skillLab->scheduled_date }}<br>{{ $skillLab->start_time }}~{{ $skillLab->end_time }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($skillLabData->lecturers as $index => $lecturer)
                <tr>
                    <td class="text-center headcol no">{{ $index + 1 }}</td>
                    <td class="headcol name">{{ $lecturer->lecturer->user->name }}</td>
                    @foreach ($skillLabData->skillsLabs as $skillLab)
                    @php
                    $isUnavailable = in_array(
                    $skillLab->id,
                    $skillLabData->unavailableSlots[$lecturer->lecturer_id] ?? [],
                    );
                    $isAssigned = $skillLab->skillslabDetails->contains(
                    'lecturer_id',
                    $lecturer->lecturer_id,
                    );
                    $isDisabled = $isUnavailable && !$isAssigned;
                    $currentAssignment = $skillLab->skillslabDetails->firstWhere(
                    'lecturer_id',
                    $lecturer->lecturer_id,
                    );
                    $currentKelompok = $currentAssignment ? $currentAssignment->kelompok_num : '';
                    @endphp

                    @if ($isDisabled && !$isAssigned)
                    <td class="text-center soft-info1">
                        -
                    </td>
                    @else
                    <td class="text-center">
                        <select class="form-select text-center input-bg kelompok-select"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $skillLab->id }}][kelompok]"
                            data-lecturer-id="{{ $lecturer->lecturer_id }}" data-skillLab-id="{{ $skillLab->id }}"
                            data-scope-id="{{ $skillLab->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                            <option value=""></option>
                            @foreach ($skillLabData->kelompok[$skillLab->group] ?? [] as $kel)
                            <option value="{{ $kel }}" {{ $currentKelompok==$kel ? 'selected' : '' }}>
                                {{ $kel }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden"
                            name="assignments[{{ $lecturer->lecturer_id }}][{{ $skillLab->id }}][assigned]"
                            value="{{ $isAssigned ? '1' : '0' }}">
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