<div class="card mb-4">
    <div class="card-header p-2 bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-uppercase text-white">PRAKTIKUM</h6>
    </div>

    <div class="card-body p-0">
        <form id="practicumForm" class="schedule-form"
            action="{{ route('admin.course.updateSchedules', $courseSchedule->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="compact-table table-bordered">
                    <thead class=" text-center">
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
                        </tr>
                    </thead>

                    <tbody id="schedule-tbody">
                        @php
                            $groupedSchedules = $schedules
                                ->groupBy(function ($item) {
                                    return strtolower($item->topic ?? '');
                                })
                                ->sortKeys();
                        @endphp

                        @foreach ($groupedSchedules as $topic => $scheduleGroup)
                            {{-- Header per topik --}}
                            <tr class="topic-group" data-topic="{{ $topic }}">
                                <td colspan="9" class="group-header" data-bs-toggle="collapse"
                                    data-bs-target="#topic-{{ Str::slug($topic) }}">
                                    <i class="fas fa-caret-down collapse-icon me-2"></i>
                                    <strong> Topik: <span class="topic-display">{{ $topic }}</span></strong>
                                </td>
                            </tr>

                            {{-- Isi per jadwal dalam topik --}}
                            @foreach ($scheduleGroup as $index => $schedule)
                                <tr class="collapse show schedule-row" id="topic-{{ Str::slug($topic) }}"
                                    data-schedule-id="{{ $schedule->id }}" data-original-topic="{{ $topic }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    @if ($schedule->zone !== null)
                                        <td class="text-center fw-semibold">
                                            <a href="#"
                                                class="delete-schedule text-danger text-decoration-underline"
                                                data-id="{{ $schedule->id }}"> DEL</a>
                                        </td>
                                    @else
                                        <td class="text-center fw-semibold">
                                            <a class="delete-schedule text-danger">DEL</a>
                                        </td>
                                    @endif
                                    {{-- contoh kode kelas --}}
                                    <td class="text-center fw-semibold">
                                        {{ $schedule->class_code ?? strtoupper(substr($schedule->activity->code ?? 'PR', 0, 2)) . sprintf('%02d', $schedule->session_number) }}
                                    </td>

                                    <input type="hidden" name="schedules[{{ $schedule->id }}][id]"
                                        value="{{ $schedule->id }}">

                                    <td class="soft-info">
                                        <input type="date" name="schedules[{{ $schedule->id }}][scheduled_date]"
                                            class="form-control text-center border-0 shadow-none bg-transparent input-bg"
                                            value="{{ $schedule->scheduled_date }}">
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
                                            class="form-control text-center border-0 shadow-none bg-transparent input-bg"
                                            value="{{ $schedule->zone }}">
                                    </td>

                                    <td>
                                        <input type="text" name="schedules[{{ $schedule->id }}][group]"
                                            id="group_{{ $schedule->id }}" class="form-control text-center input-bg"
                                            value="{{ $schedule->group }}">
                                    </td>

                                    <td class="soft-info">
                                        <input type="text" name="schedules[{{ $schedule->id }}][topic]"
                                            class="form-control text-center input-bg topic-input"
                                            value="{{ $schedule->topic }}" data-schedule-id="{{ $schedule->id }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="px-3 pb-2">
                    <small class="text-muted">
                        Grup: (A1, A2, B1, B2) / (A, B, C) / (1, 2)
                    </small>
                </div>
                <div class=" p-3">
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
</div>
