@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column">
                    <h5 class="mb-0">Absensi Diskusi {{$attendance->teachingSchedule->pemicu % 10 }} - {{
                        $attendance->teachingSchedule->activity->activity_name }}
                        {{ floor($attendance->teachingSchedule->pemicu / 10) }}
                        Blok
                        {{$course->name}}</h5>
                    <p>{{$attendance->formatted1_schedule}}</p>
                </div>

                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    <a href="{{ route('attendances.report.export', ['course' => $course->slug, 'semester_id' => $semesterId, 'session'=>$attendance->id]) }}"
                        class="btn btn-success btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET"
                    action="{{ route('attendances.report.show', ['course' => $course->slug, 'semester_id' => $semesterId, 'session'=>$attendance->id]) }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <input type="hidden" name="semester_id" value="{{ $semesterId }}" />
                            <input type="hidden" name="course" value="{{ $course->slug }}" />
                            <div class="col-md-6">
                                <label for="search" class="form-label mb-1">NIM/Name</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Cari NIM/Name" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label mb-1">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="present" {{ request('status')=='present' ? 'selected' : '' }}>Present
                                    </option>
                                    <option value="late" {{ request('status')=='late' ? 'selected' : '' }}>Late</option>
                                    <option value="absent" {{ request('status')=='absent' ? 'selected' : '' }}>Absent
                                    </option>

                                </select>
                            </div>
                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('attendances.report.show', ['course' => $course->slug, 'semester_id' => $semesterId, 'session'=>$attendance->id]) }}"
                                    class="btn btn-light btn-sm">Reset</a>
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
                                    Name
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder  text-center">
                                    NIM
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                    Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($studentAttendances as $student)
                            <tr>
                                <td class="align-middle text-sm text-center">
                                    {{ $student->courseStudent->student->user->name }}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    {{ $student->courseStudent->student->nim }}
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge 
                                        {{ $student->status === 'absent' ? 'bg-danger' : 
                                        ($student->status === 'late' ? 'bg-warning' : 
                                        ($student->status === 'present' ? 'bg-success' : 'bg-secondary')) }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada absensi yang ditemukan</p>
                                        <a href="{{ route('attendances.report.show', ['course' => $course->slug, 'semester_id' => $semesterId, 'session'=>$attendance->id]) }}"
                                            class="btn btn-sm btn-outline-primary">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$studentAttendances" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush