@extends('layouts.user_type.auth')

@section('content')
    <div class="card">
        <div class="card-header mb-0 pb-0">
            <h5 class="text-uppercase">Penjadwalan Dosen</h5>
            <div class="row">
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                <input type="hidden" name="course_id" value="{{ $course->id }}">

                <div class = "col-md-4 col-12">
                    <p><strong>Tahun Ajaran:</strong> {{ $semester->academicYear->year_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Semester:</strong> {{ $semester->semester_name }}</p>
                </div>
                <div class = "col-md-4 col-12">
                    <p><strong>Blok:</strong> {{ $course->name }}</p>
                </div>
                <div class="col-md-4 col-12 d-flex gap-2">
                    <p><strong>Tugas:</strong></p> <select name="activity_id" id="activity_id" class="form-control">
                        <option value=""></option>
                        @foreach ($activity as $act)
                            <option value="{{ $act->id }}" {{ old('activity_id') == $act->id ? 'selected' : '' }}>
                                {{ $act->activity_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="mt-3 d-flex gap-2">
                <a class="btn btn-sm btn-secondary"
                    href="{{ route('courses.edit', $course->slug) }}?semester_id={{ $semester->id }}#dosen">
                    Back
                </a>
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
                <form method="GET" action="{{ route('courses.addLecturer', [$course->slug, $semester->id]) }}">
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
                                <a href="{{ route('courses.addLecturer', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                    class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive p-0">
                <form id="kelompokForm" action="{{ route('courses.updateKelompokManual', $course->slug) }}" method="POST">
                    @csrf
                    <input type="hidden" name="semester_id" value="{{ $semesterId }}">

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

                        <tbody>
                            @foreach ($lecturers as $lecturer)
                                <tr>
                                    <td class="text-center"><input type="checkbox"></td>
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

                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.group-header').forEach(header => {
                header.addEventListener('click', () => {
                    header.classList.toggle('collapsed');
                });
            });
        });

        function deleteStudent(url, name) {
            if (confirm(`Yakin ingin menghapus mahasiswa ${name} dari course?`)) {
                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(() => alert('Terjadi kesalahan saat menghapus.'));
            }
        }
    </script>
@endsection
