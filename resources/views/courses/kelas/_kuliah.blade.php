<div class="card mb-4">
    <div class="card-header p-2 bg-secondary bg-opacity-75 text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-uppercase text-white">Kuliah</h6>
    </div>

    <form id="scheduleForm" class="schedule-form" action="{{ route('admin.course.updateSchedules', $courseSchedule->id) }}"
        method="POST">
        @csrf
        <div class="table-responsive">
            <table class="compact-table table-bordered">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Zona</th>
                        <th>Grup</th>
                        <th>Topik</th>
                        <th>Dosen</th>
                        <th>Ruang</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($schedules as $index => $schedule)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            @if ($schedule->zone !== null)
                                <td class="text-center fw-semibold">
                                    <a href="#" class="delete-schedule text-danger text-decoration-underline"
                                        data-id="{{ $schedule->id }}"> DEL</a>
                                </td>
                            @else
                                <td class="text-center fw-semibold">
                                    <a class="delete-schedule text-danger">DEL</a>
                                </td>
                            @endif

                            <td class="text-center fw-semibold">
                                {{ $schedule->class_code ?? strtoupper(substr($schedule->activity->code ?? 'K', 0, 2)) . sprintf('%02d', $schedule->session_number) }}
                            </td>

                            <input type="hidden" name="schedules[{{ $schedule->id }}][id]" value="{{ $schedule->id }}">

                            <td class="soft-info">
                                <input type="date" name="schedules[{{ $schedule->id }}][scheduled_date]"
                                    class="form-control text-center input-bg" value="{{ $schedule->scheduled_date }}">
                            </td>

                            <td>
                                <input readonly class="form-control text-center" type="text"
                                    id="start_time_{{ $schedule->id }}"
                                    value="{{ $schedule->start_time ? date('H:i', strtotime($schedule->start_time)) : '' }}">
                            </td>
                            <td>
                                <input readonly class="form-control text-center" type="text"
                                    id="end_time_{{ $schedule->id }}"
                                    value="{{ $schedule->end_time ? date('H:i', strtotime($schedule->end_time)) : '' }}">
                            </td>

                            <td class="soft-info">
                                <input type="text" name="schedules[{{ $schedule->id }}][zone]"
                                    class="form-control text-center input-bg " value="{{ $schedule->zone }}">
                            </td>

                            <td>
                                <input readonly type="text" name="schedules[{{ $schedule->id }}][group]"
                                    id="group_{{ $schedule->id }}" class="form-control text-center input-bg"
                                    value="{{ $schedule->group }}">
                            </td>

                            <td>
                                <input type="text" name="schedules[{{ $schedule->id }}][topic]"
                                    class="form-control text-center input-bg" value="{{ $schedule->topic }}">
                            </td>
                            <td class="soft-info" style="min-width: 150px">
                                <select class="form-select text-center input-bg"
                                    name="schedules[{{ $schedule->id }}][lecturer_id]">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($lecturers as $lecturer)
                                        <option value="{{ $lecturer->id }}"
                                            {{ $schedule->lecturer_id == $lecturer->id ? 'selected' : '' }}>
                                            {{ $lecturer->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" name="schedules[{{ $schedule->id }}][ruang]"
                                    class="form-control text-center " value="{{ $schedule->ruang }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="p-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save me-1"></i> Save changes
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                    Cancel changes
                </a>
            </div>
        </div>
    </form>
</div>
