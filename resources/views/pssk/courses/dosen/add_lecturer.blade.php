@extends('layouts.user_type.auth')

@section('content')
<div class="card">
    <div class="card-header mb-0 pb-0">
        <div class="mt-3 d-flex justify-content-between">
            <h5 class="text-uppercase">Penjadwalan Dosen</h5>
            <a class="btn btn-sm btn-secondary"
                href="{{ route('courses.edit', $course->slug) }}?semester_id={{ $semester->id }}&tab=dosen">
                Back
            </a>
        </div>
        <div class="row">
            <input type="hidden" name="semester_id" id="semester_id" value="{{ $semester->id }}">
            <input type="hidden" name="course_id" id="course_id" value="{{ $course->id }}">
            <input type="hidden" name="course_slug" id="course_slug" value="{{ $course->slug }}">

            <div class="col-md-4 col-12">
                <p><strong>Tahun Ajaran:</strong> {{ $semester->academicYear->year_name }}</p>
            </div>
            <div class="col-md-4 col-12">
                <p><strong>Semester:</strong> {{ $semester->semester_name }}</p>
            </div>
            <div class="col-md-4 col-12">
                <p><strong>Blok:</strong> {{ $course->name }}</p>
            </div>
            <div class="col-md-4 d-flex align-items-center col-12">
                <p class="me-2 mb-0"><strong>Tugas:</strong></p>
                <select name="activity_id" id="activity_id" class="form-select form-select-sm w-50">
                    <option value="">Pilih Tugas</option>
                    @foreach ($activity as $act)
                    <option value="{{ $act->id }}" {{ request('activity_id')==$act->id || $selectedActivity == $act->id
                        ? 'selected' : '' }}>
                        {{ $act->activity_name }}
                    </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <div class="card-body mt-0 pt-0">
        <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="mx-3 my-2 py-2">
                <div class="row g-2">
                    <!-- Input Dosen -->
                    <div class="col-md-6">
                        <label for="dosen" class="form-label mb-1">Name</label>
                        <input type="text" class="form-control form-control-sm" id="filter_name"
                            value="{{ request('name') }}" placeholder="Cari nama dosen...">
                    </div>
                    <div class="col-md-6">
                        <label for="blok" class="form-label mb-1">Bagian</label>
                        <input type="text" class="form-control form-control-sm" id="filter_bagian"
                            value="{{ request('bagian') }}" placeholder="Cari bagian...">
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('admin.courses.addLecturer', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="button" id="applyFilter" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="kelompokForm" action="{{ route('admin.courses.assignLecturer', $course->slug) }}" method="POST">
            @csrf
            <div class="table-responsive p-0">
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <input type="hidden" name="selected_activity" id="selected_activity" value="{{ $selectedActivity }}">

                <table class="compact-table table-bordered">
                    <thead class="text-center align-middle">
                        <tr>
                            <th style="width: 40px;"></th>
                            <x-sortable-th label="Name" field="name" :sort="$sort" :dir="$dir" />
                            <x-sortable-th label="Bagian" field="bagian" :sort="$sort" :dir="$dir" />
                            <th>Strata</th>
                            <th>Gelar</th>
                            <th>Tipe Dosen</th>
                            <th>NIDN</th>
                        </tr>
                    </thead>

                    <tbody id="lecturers-tbody">
                        @foreach ($lecturers as $lecturer)
                        <tr>
                            <td class="text-center clickable-td">
                                <input name="lecturers[]" value="{{ $lecturer->id }}" type="checkbox" {{
                                    in_array($lecturer->id, $assignedLecturers) ? 'checked' : '' }}>
                            </td>
                            <td>{{ $lecturer->user->name ?? '-' }}</td>
                            <td>{{ $lecturer->bagian ?? '-' }}</td>
                            <td>{{ $lecturer->strata ?? '-' }}</td>
                            <td>{{ $lecturer->gelar ?? '-' }}</td>
                            <td>{{ $lecturer->tipe_dosen ?? '-' }}</td>
                            <td>{{ $lecturer->nidn ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
            const applyBtn = document.getElementById("applyFilter");
            const activitySelect = document.getElementById('activity_id');
            const semesterId = document.getElementById('semester_id').value;
            const courseSlug = document.getElementById('course_slug').value;

            applyBtn.addEventListener('click', function(){
                   const name = document.getElementById('filter_name').value;
        const bagian = document.getElementById('filter_bagian').value;
        const activityId = document.getElementById('activity_id').value;
     const params = new URLSearchParams({
            semester_id: semesterId,
            activity_id: activityId,
            name: name,
            bagian: bagian
        });
                        window.location.href = `/course/${courseSlug}/addLecturer?${params.toString()}`;

            });
            activitySelect.addEventListener('change', function() {
                const activityId = this.value;
                const name = document.getElementById('filter_name')?.value || '';
                const bagian = document.getElementById('filter_bagian')?.value || '';

                const params = new URLSearchParams({
                    semester_id: semesterId,
                    activity_id: activityId,
                    name: name,
                    bagian: bagian
                });

                window.location.href = `/course/${courseSlug}/addLecturer?${params.toString()}`;
            });
        });
</script>
@endsection