@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    {{-- <h5 class="mb-0">List Attendance</h5>
                    @if ($semesterId)
                    @php
                    $selectedSemester = $semesters->firstWhere('id', $semesterId);
                    @endphp
                    @if ($selectedSemester)
                    <span class="badge bg-success text-white">
                        {{ $selectedSemester->semester_name }} -
                        {{ $selectedSemester->academicYear->year_name }}
                        @if ($activeSemester && $selectedSemester->id == $activeSemester->id)
                        (Aktif)
                        @endif
                    </span>
                    @endif
                    @endif --}}
                </div>

                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    {{-- <a href="{{ route('attendance.create') }}" class="btn btn-primary btn-sm"
                        style="white-space: nowrap;">
                        +&nbsp; New attendance
                    </a> --}}
                    {{-- <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button> --}}
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse {{ request()->hasAny(['semester_id', 'name']) }}" id="filterCollapse">
                <form method="GET" action="{{ route('attendance.index') }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <!-- Filter Semester (dari tabel semester) -->
                            <div class="col-md-6">
                                <label for="semester_id" class="form-label mb-1">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select">
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                        }}>
                                        {{ $semester->semester_name }} -
                                        {{ $semester->academicYear->year_name }}
                                        @if ($activeSemester && $semester->id == $activeSemester->id)
                                        (Aktif)
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Input Blok -->
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">Blok</label>
                                <input type="text" class="form-control form-control" name="name"
                                    value="{{ request('name') }}" placeholder="Kode atau nama blok">
                            </div>
                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('attendance.index') }}" class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body pb-2">
                <div id="calendar" style="max-width: 100%; width: 100%;"></div>

                {{-- <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">
                                    <a href="{{ route(
                                                'attendance.index',
                                                array_merge(request()->all(), [
                                                    'sort' => 'start_time',
                                                    'dir' => $sort === 'start_time' && $dir === 'asc' ? 'desc' : 'asc',
                                                ]),
                                            ) }}">
                                        Tanggal
                                        @if ($sort === 'start_time')
                                        <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder">Blok</th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Kegiatan
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Total
                                    Attendance
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Status
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($attendances as $attendance)
                            <tr>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $attendance->formatted_time }}</span>
                                </td>
                                <td class="">
                                    <span class="text-sm font-weight-bold">{{ $attendance->course->name }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $attendance->activity->activity_name
                                        }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $attendance->total_attendance }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $attendance->status }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn bg-gradient-primary m-1 p-2 px-3 dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false" title="Manage">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <ul class="dropdown-menu shadow">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('attendance.edit', ['attendance' => $attendance->absensi_code,]) }}">
                                                    <i class="fas fa-cog text-secondary me-2"></i> Edit
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a href="{{ route('attendance.show', $attendance->absensi_code) }}"
                                        class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada course yang ditemukan</p>
                                        <a href="{{ route('attendance.index') }}"
                                            class="btn btn-sm btn-outline-primary">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$attendances" />
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    /* Kalender bisa scroll horizontal jika space sempit */
    #calendar {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }

    .fc .fc-col-header-cell-cushion {
        color: #344767 !important
    }

    .fc .fc-toolbar-title {
        color: #344767 !important
    }

    .fc .fc-button-primary {
        background-color: rgb(141, 195, 231);
        border-color: rgb(141, 195, 231);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #344767;
        border-color: #344767;
    }

    .fc .fc-button-primary:disabled {
        background-color: #344767;
        border-color: #344767;
    }

    /* Toolbar tidak mepet dan tidak pecah */
    @media (max-width: 767px) {
        .fc-header-toolbar {
            flex-wrap: wrap !important;
            gap: 6px;
            justify-content: center;
        }

        .fc-toolbar-chunk {
            width: 100%;
            text-align: right;
        }

        /* Untuk mempersempit kolom jam jika layar sangat kecil */
        .fc-timegrid-slot-label {
            font-size: 5px !important;
        }

        .fc-timegrid-event {
            font-size: 11px;
        }

    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        if (!calendarEl) return;

        // Deteksi jika device mobile
        const isMobile = window.innerWidth <= 768;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: isMobile ? 'timeGridDay' : 'timeGridWeek', // Day untuk mobile, Week untuk desktop
            nowIndicator: true,
            allDaySlot: false,
            slotMinTime: "07:00:00",
            slotMaxTime: "17:00:00",
            events: '{{ route('attendances.json') }}',
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: true
            },
            headerToolbar: {
                right: 'prev,next today',
                center: 'title',
                left: isMobile ? '' : 'dayGridMonth,timeGridWeek,timeGridDay' // Sesuaikan toolbar
            },
            dayHeaderFormat: {
            day: 'numeric',
            month: 'short'
              },
            height: 'auto'
        });

        calendar.render();
    });
</script>
@push('dashboard')
@endpush