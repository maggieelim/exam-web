@extends('layouts.user_type.auth')

@section('content')
<div class="card">
    <div class="card-body px-0 pt-0 pb-2">
        <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="collapse" id="filterCollapse">
            <form method="GET" action="{{ route('admin.lecturer-recap.index') }}">
                <div class="mx-3 my-2 py-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label mb-1">Semester</label>
                            <select name="semester_id" class="form-select">
                                @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : '' }}>
                                    {{ $semester->semester_name }}
                                    - {{ $semester->academicYear->year_name }}
                                    @if ($activeSemester && $semester->id == $activeSemester->id)
                                    (Active)
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('admin.lecturer-recap.index') }}" class="btn btn-light btn-sm">
                                Reset
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
        <div class="table-wrapper p-0">
            <table class="compact-table table-bordered">
                @php
                $groupedCourses = $courses->groupBy('sesi');
                @endphp
                <thead class="text-center align-middle">
                    <tr>
                        <th rowspan="3" class="headcol">Nama Dosen</th>
                        @foreach ($groupedCourses as $sesi => $courseGroup)
                        <th colspan="{{ $courseGroup->count() * $activities->count() }}">
                            Sesi {{ $sesi ?? '-' }}
                        </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($groupedCourses as $courseGroup)
                        @foreach ($courseGroup as $course)
                        <th colspan="{{ $activities->count() }}">
                            {{ $course->name }}
                        </th>
                        @endforeach
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($courses as $course)
                        @foreach ($activities as $activity)
                        <th class="text-center text-xs text-wrap">
                            {{ $activity }}
                        </th>
                        @endforeach
                        @endforeach
                    </tr>
                </thead>


                <tbody>
                    @foreach ($lecturers as $lecturer)
                    <tr>
                        <td class="headcol text-wrap">
                            {{ $lecturer->user->name }}
                        </td>

                        @foreach ($courses as $course)
                        @foreach ($activities as $activity)
                        <td class="text-center">
                            {{ $summary[$lecturer->id][$course->id][$activity] ?? 0 }}
                        </td>
                        @endforeach
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>

            </table>
            {{-- <div class="d-flex justify-content-center mt-3">
                <x-pagination :paginator="$lecturers" />
            </div> --}}
        </div>
    </div>
</div>
@endsection