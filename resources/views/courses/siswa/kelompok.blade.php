@extends('layouts.user_type.auth')

@section('content')
    <div class="card">
        <div class="card-header mb-0 pb-0">
            <h5 class="text-uppercase">Bentuk Kelompok</h5>
            <form id="kelompokForm" action="{{ route('courses.updateKelompok', [$course->slug, $semester->id]) }}"
                method="POST">
                @csrf
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
                    <div class = "col-md-4 col-12">
                        <p><strong>Total Mahasiswa:</strong> {{ $studentData->students->count() }}
                        </p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center col-12">
                        <p class="me-2 mb-0"><strong>Jumlah per-Kelompok:</strong></p>
                        <input type="number" id="kelompok" name="kelompok" class="form-control form-control-sm w-auto"
                            min="1" max="{{ $studentData->students->count() }}"
                            value="{{ old('kelompok', $jumlahPerKelompok ?? '10') }}" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">Bentuk Kelompok </button>
                    <a class="btn btn-sm btn-secondary"
                        href="{{ route('courses.edit', $course->slug) }}?semester_id={{ $semester->id }}#siswa">
                        Back
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body mt-0 pt-0">
            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('courses.createKelompok', [$course->slug, $semester->id]) }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <!-- Input Blok -->
                            <input type="hidden" class="form-control" name="semester_id"
                                value="{{ request('semester_id') }}">
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">NIM</label>
                                <input type="text" class="form-control form-control-sm" name="nim"
                                    value="{{ request('nim') }}">
                            </div>

                            <!-- Input Dosen -->
                            <div class="col-md-6">
                                <label for="dosen" class="form-label mb-1">Name</label>
                                <input type="text" class="form-control form-control-sm" name="name"
                                    value="{{ request('name') }}">
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('courses.createKelompok', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
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
                                <th>#</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Gender</th>
                                <th>Kelompok</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($studentData->groupedStudents as $kelompok => $students)
                                {{-- Header per kelompok --}}
                                <tr>
                                    <td colspan="6" class="group-header" data-bs-toggle="collapse"
                                        data-bs-target="#group-{{ $kelompok }}">
                                        <i class="fas fa-caret-down collapse-icon"></i>
                                        Kelompok: {{ $kelompok }}
                                        (Jumlah={{ $students->count() }} Siswa)
                                    </td>
                                </tr>

                                {{-- Isi anggota kelompok --}}
                                @foreach ($students as $index => $studentUser)
                                    <tr class="collapse show" id="group-{{ $kelompok }}">
                                        <td style="width: 40px;">
                                            <button type="button"
                                                class="btn btn-link text-danger text-decoration-underline p-0 m-0"
                                                onclick="deleteStudent('{{ route('courses.student.destroy', [$course->slug, $studentUser->id]) }}', '{{ $studentUser->student->user->name }}')">
                                                Del
                                            </button>

                                        </td>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $studentUser->student->nim ?? '-' }}</td>
                                        <td>{{ $studentUser->student->user->name ?? '-' }}</td>
                                        <td>{{ $studentUser->student->user->gender ?? '-' }}</td>
                                        <td style="width: 100px;">
                                            <input type="number" class="form-control form-control-sm text-center input-bg"
                                                name="kelompok[{{ $studentUser->id }}]"
                                                value="{{ $studentUser->kelompok ?? '' }}"
                                                data-original-value="{{ $studentUser->kelompok ?? '' }}" min="1">
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            <tr>
                                <td colspan="6" class="group-header">
                                    <strong>Total Siswa: {{ $studentData->students->count() }}</strong>
                                </td>
                            </tr>
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
