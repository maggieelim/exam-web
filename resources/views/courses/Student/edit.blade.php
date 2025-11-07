@extends('layouts.user_type.auth')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">MAHASISWA BLOK {{ $course->name }}</h5>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn bg-gradient-primary btn-sm" type="button" data-bs-toggle="modal"
                            data-bs-target="#addStudentModal">
                            +&nbsp; Add Students
                        </button>
                    </div>
                </div>

                <!-- Collapse Form -->
                <div class="collapse" id="filterCollapse">
                    <form method="GET" action="{{ route('admin.courses.editStudent', [$course->slug]) }}">
                        <div class="mx-3 my-2 py-2">
                            <div class="row g-2">
                                <!-- Input Blok -->
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
                                    <a href="{{ route('admin.courses.editStudent', [$course->slug]) }}"
                                        class="btn btn-light btn-sm">Reset</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    {{-- Kolom NIM --}}
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                        <a href="{{ route('admin.courses.editStudent', $course->slug) }}?{{ http_build_query(
                                            array_merge(request()->except('page'), [
                                                'sort' => 'nim',
                                                'dir' => $sort === 'nim' && $dir === 'asc' ? 'desc' : 'asc',
                                            ]),
                                        ) }}"
                                            class="text-dark text-decoration-none">
                                            NIM
                                            @if ($sort === 'nim')
                                                <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Kolom Nama --}}
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                        <a href="{{ route('admin.courses.editStudent', $course->slug) }}?{{ http_build_query(
                                            array_merge(request()->except('page'), [
                                                'sort' => 'name',
                                                'dir' => $sort === 'name' && $dir === 'asc' ? 'desc' : 'asc',
                                            ]),
                                        ) }}"
                                            class="text-dark text-decoration-none">
                                            Nama
                                            @if ($sort === 'name')
                                                <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                        Email
                                    </th>
                                    {{-- Kolom Action --}}
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($students as $studentUser)
                                    <tr>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold">
                                                {{ $studentUser->student->nim ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold">
                                                {{ $studentUser->student->user->name }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold">
                                                {{ $studentUser->student->user->email }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <form
                                                action="{{ route('admin.courses.student.destroy', [$course->slug, $studentUser->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini dari course?')"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn bg-gradient-danger m-1 p-2 px-3"
                                                    title="Hapus Mahasiswa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            <x-pagination :paginator="$students" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Student -->
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
                            <input type="file" name="excel" id="excel" class="form-control"
                                accept=".xlsx,.xls,.csv">

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
@endsection
