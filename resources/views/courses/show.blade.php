@extends('layouts.user_type.auth')

@section('content')
<div class="card mb-4 p-3">
    <div class="pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
        <div>
            <h5 class="mb-0">{{ $course->name }}</h5>
        </div>
        <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
            {{-- Tombol Download --}}
            <a href="{{ route('courses.download', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                class="btn btn-sm btn-success">
                <i class="fas fa-download me-2"></i>
                Export
            </a>
            @role('admin')
            {{-- Tombol Delete --}}
            <form action="{{ route('courses.destroy', $course->slug) }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus course ini?')" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                    Delete
                </button>
            </form>
            @endrole
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Kode Blok:</strong> {{ $course->kode_blok }}</p>
        </div>
        <div class="col-md-6 d-flex gap-2">
            <p><strong>Koordinator:</strong></p>
            <ul class="mb-0 ps-3">
                @foreach ($lecturers as $lecturer)
                <li>{{ $lecturer->lecturer->user->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>

</div>

<!-- Card Daftar Mahasiswa -->
<div class="card mb-4">
    <div class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
        <h5>List Students</h5>
        <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
            {{-- <a href="{{ route('admin.courses.editStudent', [$course->slug, 'semester_id' => $semesterId]) }}"
                class="btn btn-warning">
                <i class="fas fa-edit"></i>
            </a> --}}
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('courses.show', $course->slug) }}">
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            <div class="mx-3 ">
                <div class="row g-2">
                    <!-- Input Blok -->
                    <div class="col-md-12">
                        <label for="search" class="form-label mb-1">NIM/Nama</label>
                        <input type="text" name="search" class="form-control form-control" placeholder="Filter NIM/Nama"
                            value="{{ request('search') }}">
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('courses.show', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
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
                        <th class=" text-center text-uppercase text-dark text-sm font-weight-bolder">No</th>
                        <th class=" text-uppercase text-dark text-sm font-weight-bolder">NIM</th>
                        <th class=" text-uppercase text-dark text-sm font-weight-bolder">Nama</th>
                        <th class=" text-uppercase text-dark text-sm font-weight-bolder">Email</th>
                        <th class=" text-uppercase text-dark text-sm font-weight-bolder">Gender</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Kelompok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $studentUser)
                    @php
                    $student = $studentUser->student;
                    $user = $student?->user;
                    @endphp
                    <tr>
                        <td class="text-sm text-center">{{ $students->firstItem() + $index }}</td>
                        <td class="text-sm">{{ $student->nim ?? '-' }}</td>
                        <td class="text-sm">{{ $user->name ?? '-' }}</td>
                        <td class="text-sm">{{ $user->email ?? '-' }}</td>
                        <td class="text-sm">{{ $user->gender ?? '-' }}</td>
                        <td class="text-sm text-center">{{ $studentUser->kelompok ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$students" />
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush