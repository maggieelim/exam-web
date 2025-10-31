<div class="card mb-4">
    <div class="card-header p-2 bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-uppercase text-white">TUTOR / PEMICU</h6>
    </div>

    <div class="card-body p-0">
        <form action="{{ route('admin.course.updateSchedules', $courseSchedule->id) }}" method="POST">
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
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($schedules as $index => $schedule)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>

                                <td class="text-center fw-semibold">
                                    <a href="" class=" text-info text-decoration-underline">
                                        PRE</a>
                                </td>
                                <td class="text-center fw-semibold">
                                    kelas
                                </td>

                                <input type="hidden" name="schedules[{{ $schedule->id }}][id]"
                                    value="{{ $schedule->id }}">

                                <td class="soft-info">
                                    <input type="date" name="schedules[{{ $schedule->id }}][scheduled_date]"
                                        class="form-control text-center  " value="{{ $schedule->scheduled_date }}">
                                </td>

                                <td>
                                    <input readonly class="form-control text-center  " type="text"
                                        value="{{ $schedule->start_time ? date('H:i', strtotime($schedule->start_time)) : '' }}">
                                </td>
                                <td>
                                    <input readonly class="form-control text-center  " type="text"
                                        value="{{ $schedule->end_time ? date('H:i', strtotime($schedule->end_time)) : '' }}">
                                </td>

                                <td class="soft-info">
                                    <input type="text" name="schedules[{{ $schedule->id }}][zone]"
                                        class="form-control text-center  " value="{{ $schedule->zone }}">
                                </td>

                                <td>
                                    <input type="text" name="schedules[{{ $schedule->id }}][group]"
                                        class="form-control text-center  " value="{{ $schedule->group }}">
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
