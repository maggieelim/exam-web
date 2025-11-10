@extends('layouts.user_type.auth')

@section('content')
    <div class="card">
        <div class="card-header mb-0 pb-0">
            <div class="mt-3 d-flex justify-content-between">
                <h5 class="text-uppercase">Penjadwalan Dosen</h5>
                <a class="btn btn-sm btn-secondary"
                    href="{{ route('courses.edit', $course->slug) }}?semester_id={{ $semester->id }}#dosen">
                    Back
                </a>
            </div>
            <div class="row">
                <input type="hidden" name="semester_id" id="semester_id" value="{{ $semester->id }}">
                <input type="hidden" name="course_id" id="course_id" value="{{ $course->id }}">
                <input type="hidden" name="course_slug" id="course_slug" value="{{ $course->slug }}">

                <div class = "col-md-4 col-12">
                    <p><strong>Tahun Ajaran:</strong> {{ $semester->academicYear->year_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Semester:</strong> {{ $semester->semester_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Blok:</strong> {{ $course->name }}</p>
                </div>
                <div class="col-md-4 d-flex align-items-center col-12">
                    <p class="me-2 mb-0"><strong>Tugas:</strong></p>
                    <select name="activity_id" id="activity_id" class="form-select form-select-sm w-50"
                        onchange="updateLecturersByActivity(this.value)">
                        <option value="">Pilih Tugas</option>
                        @foreach ($activity as $act)
                            <option value="{{ $act->id }}" {{ old('activity_id') == $act->id ? 'selected' : '' }}>
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
                <form method="GET" action="{{ route('admin.courses.addLecturer', [$course->slug, $semester->id]) }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <!-- Input Blok -->
                            <input type="hidden" class="form-control" name="semester_id"
                                value="{{ request('semester_id') }}">
                            <!-- Input Dosen -->
                            <div class="col-md-6">
                                <label for="dosen" class="form-label mb-1">Name</label>
                                <input type="text" class="form-control form-control-sm" name="name"
                                    value="{{ request('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">Bagian</label>
                                <input type="text" class="form-control form-control-sm" name="bagian"
                                    value="{{ request('bagian') }}">
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('admin.courses.addLecturer', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                    class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <form id="kelompokForm" action="{{ route('admin.courses.assignLecturer', $course->slug) }}" method="POST">
                @csrf
                <div class="table-responsive p-0">
                    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                    <input type="hidden" id="selectedActivity" name="selected_activity" value="">

                    <table class="compact-table table-bordered">
                        <thead class="text-center align-middle">
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>Nama</th>
                                <th>Bagian</th>
                                <th>Strata</th>
                                <th>Gelar</th>
                                <th>Tipe Dosen</th>
                                <th>NIDN</th>
                            </tr>
                        </thead>

                        <tbody id="lecturers-tbody">
                            @foreach ($lecturers as $lecturer)
                                <tr>
                                    <td class="text-center">
                                        <input name="lecturers[]" value="{{ $lecturer->id }}" type="checkbox"
                                            {{ in_array($lecturer->id, $assignedLecturers) ? 'checked' : '' }}>
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
        document.addEventListener('DOMContentLoaded', () => {
            const activitySelect = document.querySelector('#activity_id');
            const selectedActivityInput = document.querySelector('#selectedActivity');

            // Saat dropdown berubah, update hidden input
            activitySelect.addEventListener('change', () => {
                selectedActivityInput.value = activitySelect.value;
            });

            // Jika ingin memastikan nilai tetap tersimpan setelah reload (misalnya pakai old input)
            selectedActivityInput.value = activitySelect.value;
        });

        function updateLecturersByActivity(activityId) {
            const semesterId = document.getElementById('semester_id').value;
            const courseId = document.getElementById('course_id').value;
            const courseSlug = document.getElementById('course_slug').value;
            const tbody = document.getElementById('lecturers-tbody');

            // Show loading
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';

            // Update hidden input
            document.getElementById('selectedActivity').value = activityId;

            // AJAX request to get lecturers data based on activity
            fetch(`/admin/course/${courseSlug}/get-lecturers?semester_id=${semesterId}&activity_id=${activityId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear tbody
                    tbody.innerHTML = '';

                    // Populate with new data
                    data.lecturers.forEach(lecturer => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">
                                <input name="lecturers[]" value="${lecturer.id}" type="checkbox" ${lecturer.assigned ? 'checked' : ''}>
                            </td>
                            <td>${lecturer.user.name || '-'}</td>
                            <td>${lecturer.bagian || '-'}</td>
                            <td>${lecturer.strata || '-'}</td>
                            <td>${lecturer.gelar || '-'}</td>
                            <td>${lecturer.tipe_dosen || '-'}</td>
                            <td>${lecturer.nidn || '-'}</td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML =
                        '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
                });
        }
    </script>
@endsection
