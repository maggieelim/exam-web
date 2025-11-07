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


<form class="schedule-form" action="{{ route('admin.course.assignPleno') }}" method="POST">
    @csrf
    <div class="table-responsive p-0">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th>No</th>
                    <th>Nama Dosen</th>
                    <th>Bagian</th>
                    @foreach ($plenoData->plenos as $pleno)
                        <th>P{{ $pleno->session_number }}
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
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $lecturer->lecturer->user->name }}</td>
                        <td>{{ $lecturer->lecturer->bagian }}</td>
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
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $pleno->id }}]"
                                        value="1"
                                        {{ $pleno->plenoDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
                                </td>
                            @else
                                <td class="text-center">
                                    <input type="checkbox" class="group-checkbox input-bg"
                                        name="assignments[{{ $lecturer->lecturer_id }}][{{ $pleno->id }}]"
                                        value="1"
                                        {{ $pleno->plenoDetails->contains('lecturer_id', $lecturer->lecturer_id) ? 'checked' : '' }}>
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
