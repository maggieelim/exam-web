@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12 card mb-4">
        <div class="card-header d-flex flex-row justify-content-between mb-0 pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                <h5 class="mb-0">Previous Attendance</h5>
                @if ($semesterId)
                @php
                $selectedSemester = $semesters->firstWhere('id', $semesterId);
                @endphp
                <x-semester-badge :semester="$selectedSemester" :activeSemester="$activeSemester" />
                @endif
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>

        <div class="collapse" id="filterCollapse">
            <form method="GET" action="{{ route('student.attendance.index') }}">
                <div class="mx-3 mb-2 pb-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="semester_id" class="form-label mb-1">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-select">
                                @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                    }}>
                                    {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                                    @if ($activeSemester && $semester->id == $activeSemester->id)
                                    (Active)
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="blok" class="form-label mb-1">Status</label>
                            <select name="status" class="form-control">
                                <option value="">-- ALL --</option>
                                <option value="present" {{ request('status')=='present' ? 'selected' : '' }}>Present
                                </option>
                                <option value="late" {{ request('status')=='late' ? 'selected' : '' }}>Late</option>
                                <option value="absent" {{ request('status')=='absent' ? 'selected' : '' }}>Absent
                                </option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('student.attendance.index') }}" class="btn btn-light btn-sm">Reset</a>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0 text-wrap">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-wrap text-center">
                                Course
                            </th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder  text-center">
                                Activity
                            </th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                Date</th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                Clocked in at</th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($attendances as $attendance)
                        <tr>
                            <td class="align-middle text-sm text-center">
                                {{ $attendance->courseStudent->course->name }}
                            </td>
                            <td class="align-middle text-sm text-center">
                                {{ $attendance->session->activity->activity_name }}
                            </td>
                            <td class="align-middle text-center text-sm">
                                {{ $attendance->session ?
                                \Carbon\Carbon::parse($attendance->session->start_time)->translatedFormat('l, d M Y
                                H:i')
                                : '-' }}
                            </td>
                            <td class="align-middle text-center text-sm">
                                {{ $attendance->scanned_at ?
                                \Carbon\Carbon::parse($attendance->scanned_at)->format('H:i')
                                : '-' }}
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge 
                                        {{ $attendance->status === 'absent' ? 'bg-danger' : 
                                        ($attendance->status === 'late' ? 'bg-warning' : 
                                        ($attendance->status === 'present' ? 'bg-success' : 'bg-secondary')) }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Tidak ada absensi yang ditemukan</p>
                                    <a href="{{ route('student.attendance.index') }}"
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush