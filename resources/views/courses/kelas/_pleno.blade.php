<div class="card my-2 border">
    <div class="card-header p-2 bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-uppercase text-white">PLENO</h6>
    </div>

    <div class="card-body p-0">
        <form action="{{ route('admin.course.updateSchedules', $courseSchedule->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="compact-table table-bordered">
                    <thead class=" text-center">
                        <tr>
                            <th>#</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Zona</th>
                            <th>Grup</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($schedules as $index => $schedule)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>

                                {{-- contoh: PR01, PR02, UP1, UP2 --}}
                                <td class="text-center fw-semibold">
                                    {{ $schedule->class_code ?? strtoupper(substr($schedule->activity->code ?? 'P', 0, 2)) . sprintf('%02d', $schedule->session_number) }}
                                </td>

                                <input type="hidden" name="schedules[{{ $schedule->id }}][id]"
                                    value="{{ $schedule->id }}">

                                <td class="soft-info">
                                    <input type="date" name="schedules[{{ $schedule->id }}][scheduled_date]"
                                        class="form-control text-center border-0 shadow-none bg-transparent"
                                        value="{{ $schedule->scheduled_date }}">
                                </td>

                                <td>
                                    <input readonly class="form-control text-center border-0 shadow-none bg-transparent"
                                        type="text"
                                        value="{{ $schedule->start_time ? date('H:i', strtotime($schedule->start_time)) : '' }}">
                                </td>
                                <td>
                                    <input readonly class="form-control text-center border-0 shadow-none bg-transparent"
                                        type="text"
                                        value="{{ $schedule->end_time ? date('H:i', strtotime($schedule->end_time)) : '' }}">
                                </td>

                                <td class="soft-info">
                                    <input type="text" name="schedules[{{ $schedule->id }}][zone]"
                                        class="form-control text-center border-0 shadow-none bg-transparent"
                                        value="{{ $schedule->zone }}">
                                </td>

                                <td>
                                    <input type="text" name="schedules[{{ $schedule->id }}][group]"
                                        class="form-control text-center border-0 shadow-none bg-transparent"
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
