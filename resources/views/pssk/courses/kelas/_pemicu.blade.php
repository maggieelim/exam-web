<div class="card mb-4">
    <div class="card-header p-2 bg-secondary text-white">
        <h6 class="mb-0 text-uppercase text-white">TUTOR / PEMICU</h6>
    </div>

    <div class="card-body p-0">
        <form id="pemicuForm" class="schedule-form"
            action="{{ route('admin.course.updateSchedules', $kelasData->courseSchedule->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="compact-table table-bordered">
                    <thead class=" text-center">
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th></th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Zona</th>
                            <th>Grup</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                        // Group schedules by PRE groups (11-12, 21-22, etc)
                        $groupedSchedules = [];
                        foreach ($schedules as $schedule) {
                        $pemicuNumber = intval($schedule->pemicu_ke);
                        $preGroup = floor(($pemicuNumber - 11) / 10) + 1; // PRE 1 untuk 11-12, PRE 2 untuk 21-22
                        $groupedSchedules[$preGroup][] = $schedule;
                        }
                        @endphp

                        @foreach ($groupedSchedules as $preGroup => $groupSchedules)
                        @foreach ($groupSchedules as $index => $schedule)
                        @php
                        $pemicuNumber = intval($schedule->pemicu_ke);

                        // Get all pemicu_detail_ids from this PRE group
                        $allPemicuDetailIds = collect($groupSchedules)
                        ->map(function($sched) {
                        return $sched->pemicuDetails->pluck('id')->toArray();
                        })
                        ->flatten()
                        ->filter()
                        ->values()
                        ->toArray();

                        @endphp
                        <tr>
                            <td class="text-center">{{$loop->parent->index * 2 + $index + 1 }}</td>

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

                            @if ($index === 0)
                            @php
                            $scheduleIds = collect($groupSchedules)->pluck('id')->toArray();
                            @endphp
                            <td rowspan="2" class="text-center fw-semibold align-middle">
                                <a href="{{ route('course.nilaiPemicu', ['id1' => $scheduleIds[0], 'id2' => $scheduleIds[1]]) }}"
                                    class="text-info text-decoration-underline pre-link">
                                    PRE {{ $preGroup }}
                                </a>
                            </td>
                            @endif

                            <td class="text-center fw-semibold">
                                {{ $schedule->pemicu_ke }}
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
                                    class="form-control text-center input-bg" value="{{ $schedule->zone }}">
                            </td>
                            <td>
                                <input type="text" name="schedules[{{ $schedule->id }}][group]"
                                    id="group_{{ $schedule->id }}" class="form-control text-center input-bg"
                                    value="{{ $schedule->group }}">
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end align-items-center">

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

        {{-- catatan kecil di bawah tabel --}}

    </div>
</div>