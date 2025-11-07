@extends('layouts.user_type.auth')

@section('content')
    <div class="d-flex flex-wrap gap-2">
        <div>
            <h5 class="mb-0">MAHASISWA BLOK {{ $course->name }}</h5>
        </div>
        <div class="d-flex gap-2 ms-auto"> <!-- tambahkan ms-auto di sini -->
            <button style="height: 32px;" class="btn bg-gradient-primary d-flex align-items-center justify-content-center"
                type="button" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                +&nbsp; Add Students
            </button>
            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>

    <!-- Collapse Form -->
    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('admin.courses.editStudent', [$course->slug]) }}">
            <div class="row g-2">
                <!-- Input Blok -->
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
                    <a href="{{ route('admin.courses.editStudent', [$course->slug]) }}"
                        class="btn btn-light btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </div>
        </form>
    </div>
    @foreach ($students as $studentUser)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex flex-column h-100">
                        <!-- Header dengan nama dan delete button -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="font-weight-bolder mb-0 text-truncate pe-2">
                                {{ $studentUser->student->user->name }}
                            </h5>
                            <form action="{{ route('admin.courses.student.destroy', [$course->slug, $studentUser->id]) }}"
                                method="POST" onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini dari course?')"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm m-0 p-1" title="Hapus Mahasiswa"
                                    style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-trash fa-xs"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Informasi mahasiswa -->
                        <div class="p-0 m-0">
                            <p class="mb-2 text-secondary">
                                <i class="fas fa-id-card me-2"></i>
                                {{ $studentUser->student->nim ?? '-' }}
                            </p>
                            <p class="mb-0 text-secondary">
                                <i class="fas fa-envelope me-2"></i>
                                <small>{{ $studentUser->student->user->email }}</small>
                            </p>
                        </div>

                        <!-- Additional info jika ada -->
                        @if ($studentUser->student->angkatan || $studentUser->student->user->gender)
                            <div class="mt-3 pt-2 border-top">
                                @if ($studentUser->student->angkatan)
                                    <small class="text-muted me-3">
                                        <i class="fas fa-calendar me-1"></i>Angkatan {{ $studentUser->student->angkatan }}
                                    </small>
                                @endif
                                @if ($studentUser->student->user->gender)
                                    <small class="text-muted">
                                        <i
                                            class="fas fa-{{ $studentUser->student->user->gender === 'Laki-laki' ? 'male' : 'female' }} me-1"></i>
                                        {{ $studentUser->student->user->gender }}
                                    </small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$students" />
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
@push('dashboard')
