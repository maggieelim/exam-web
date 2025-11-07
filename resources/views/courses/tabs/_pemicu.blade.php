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


<form class="schedule-form" action="{{ route('admin.course.assignPemicu') }}" method="POST">
    @csrf
    <div class="table-responsive p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th rowspan="2"></th>
                    <th rowspan="2">No</th>
                    <th rowspan="2">TD</th>
                    <th rowspan="2">Nama Dosen</th>
                    @foreach ($pemicuData->tutors->take(ceil($pemicuData->tutors->count() / 2)) as $tutor)
                        <th colspan="2">Pemicu {{ $tutor->session_number }}</th>
                    @endforeach

                </tr>
                <tr>
                    @foreach ($pemicuData->tutors as $tutor)
                        <th>{{ $tutor->scheduled_date }}<br>{{ $tutor->start_time }}~{{ $tutor->end_time }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($pemicuData->lecturers as $index => $lecturer)
                    <tr>
                        <td class="text-center">
                            <a href="#" class="text-danger text-decoration-underline delete-lecturer"
                                data-id="{{ $lecturer->id }}"> DEL</a>
                        </td>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $lecturer->lecturer->tipe_dosen }}</td>
                        <td>{{ $lecturer->lecturer->user->name }}</td>
                        @foreach ($pemicuData->tutors as $tutor)
                            @php
                                $isUnavailable = in_array(
                                    $tutor->id,
                                    $pemicuData->unavailableSlots[$lecturer->lecturer_id] ?? [],
                                );
                                $isAssigned = $tutor->pemicuDetails->contains('lecturer_id', $lecturer->lecturer_id);
                                $isDisabled = $isUnavailable && !$isAssigned;
                                $currentAssignment = $tutor->pemicuDetails->firstWhere(
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
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $tutor->id }}][kelompok]"
                                        data-lecturer-id="{{ $lecturer->lecturer_id }}"
                                        data-tutor-id="{{ $tutor->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                        <option value=""></option>
                                        @foreach ($pemicuData->kelompok as $kel)
                                            <option value="{{ $kel }}"
                                                {{ $currentKelompok == $kel ? 'selected' : '' }}>
                                                {{ $kel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden"
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $tutor->id }}][assigned]"
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
