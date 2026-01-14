<div class="d-flex flex-wrap justify-content-between gap-2">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <a class="btn btn-outline-info px-3 py-2"
            href="{{ route('admin.courses.downloadDaftarSiswa', ['course' => $course->slug, 'semesterId' => $semesterId]) }}"
            title="Download Excel">
            <i class="fas fa-download"></i>
        </a>

        <button class="btn btn-primary px-3 py-2" type="button" data-bs-toggle="modal"
            data-bs-target="#addStudentModal">
            <i class="fas fa-user-plus"></i>
            <span class="d-none d-md-inline ms-1">Siswa</span>
        </button>

        <a class="btn btn-primary px-3 py-2"
            href="{{ route('admin.courses.createKelompok', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
            <i class="fas fa-users d-none d-md-inline"></i>
            <span class="ms-md-1">Kelompok</span>
        </a>

        <a class="btn btn-primary px-3 py-2"
            href="{{ route('admin.courses.createGroup', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
            <i class="fas fa-user-group d-none d-md-inline"></i>
            <span class="ms-md-1">Grup</span>
        </a>
    </div>

    <div>
        <button class="btn px-3 py-2 btn-outline-secondary" type="button" data-bs-toggle="collapse"
            data-bs-target="#filterCollapse">
            <i class="fas fa-filter"></i>
            <span class="d-none d-md-inline ms-1">Filter</span>
        </button>
    </div>
</div>


<!-- Collapse Form -->
<div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('courses.edit', [$course->slug]) }}">
        <div class="mx-3 my-2 py-2">
            <div class="row g-2">
                <!-- Input Blok -->
                <input type="hidden" name="semester_id" value="{{ request('semester_id') }}">
                <input type="hidden" name="tab" value="siswa">
                <div class="col-md-6">
                    <label for="blok" class="form-label mb-1">NIM</label>
                    <input type="text" class="form-control form-control-sm" name="nim" value="{{ request('nim') }}">
                </div>

                <!-- Input Dosen -->
                <div class="col-md-6">
                    <label for="dosen" class="form-label mb-1">Name</label>
                    <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}">
                </div>

                <!-- Buttons -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('courses.edit', [$course->slug, 'semester_id' => request('semester_id'), 'tab' => request('tab')]) }}"
                        class="btn btn-light btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive p-0">
    <table class="compact-table table-bordered">
        <thead class="text-center align-middle">
            <tr>
                <th>#</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Gender</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($studentData->groupedStudents as $kelompok => $students)
            <tr>
                <td colspan="4" class="group-header" data-bs-toggle="collapse" data-bs-target="#group-{{ $kelompok }}">
                    <i class="fas fa-caret-down collapse-icon"></i>
                    Kelompok: {{ $kelompok }} <span class="text-muted">(Jumlah={{ $students->count() }}
                        Siswa)</span>
                </td>

            </tr>

            {{-- Isi anggota kelompok --}}
            @foreach ($students as $index => $studentUser)
            <tr class="collapse show" id="group-{{ $kelompok }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $studentUser->student->nim ?? '-' }}</td>
                <td>{{ $studentUser->student->user->name ?? '-' }}</td>
                <td>{{ $studentUser->student->user->gender ?? '-' }}</td>

            </tr>
            @endforeach
            @endforeach
            <tr>
                <td colspan="5" class="group-header"><strong> Total Siswa:
                        {{ $studentData->students->count() }}
                    </strong> </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.courses.addStudent', $course->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Tambah Mahasiswa ke Blok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Upload Excel Section -->
                    <div>
                        <label for="excel" class="form-label">Upload File Excel</label>
                        <input type="file" name="excel" id="excel" class="form-control" accept=".xlsx,.xls,.csv">

                        <div class="mt-2">
                            <a href="{{ asset('templates/import_mahasiswa_peserta_blok.xlsx') }}"
                                class="btn btn-info btn-sm" download>
                                <i class="fas fa-file-excel me-1"></i>Download Template
                            </a>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="d-flex align-items-center">
                        <hr class="flex-grow-1">
                        <span class="mx-3 text-muted">atau</span>
                        <hr class="flex-grow-1">
                    </div>

                    <!-- Manual NIM Input -->
                    <div class="mb-3">
                        <label for="nim" class="form-label">Masukkan NIM Manual</label>
                        <textarea class="form-control" name="nim" id="nim" rows="5"
                            placeholder="Pisahkan dengan Enter. Contoh:&#10;134561&#10;156782&#10;179003"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>