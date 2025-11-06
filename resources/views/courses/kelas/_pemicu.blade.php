<div class="card mb-4">
    <div class="card-header p-2 bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-uppercase text-white">TUTOR / PEMICU</h6>
    </div>

    <div class="card-body p-0">
        <form id="pemicuForm" class="schedule-form"
            action="{{ route('admin.course.updateSchedules', $courseSchedule->id) }}" method="POST">
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
                            $kelas = $schedules->count();
                            $bagian = ceil($kelas / 2); // Membagi menjadi 2 bagian
                        @endphp
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
                                    <a href="" class=" text-info text-decoration-underline">
                                        PRE</a>
                                </td>
                                <td class="text-center fw-semibold">
                                    @php
                                        $kelompok = floor($index / 2) + 1;
                                        $urutan = ($index % 2) + 1;
                                        echo $kelompok . $urutan;
                                    @endphp
                                </td>

                                <input type="hidden" name="schedules[{{ $schedule->id }}][id]"
                                    value="{{ $schedule->id }}">

                                <td class="soft-info">
                                    <input type="date" name="schedules[{{ $schedule->id }}][scheduled_date]"
                                        class="form-control text-center input-bg"
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
                                        class="form-control text-center input-bg " value="{{ $schedule->zone }}">
                                </td>
                                <td>
                                    <input type="text" name="schedules[{{ $schedule->id }}][group]"
                                        id="group_{{ $schedule->id }}" class="form-control text-center input-bg"
                                        value="{{ $schedule->group }}">
                                </td>
                            </tr>
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
