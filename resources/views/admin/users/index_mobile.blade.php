@extends('layouts.user_type.auth')

@section('content')
    <div class="d-flex justify-content-between gap-2">
        <div>
            <h5 class="mb-0">List {{ ucfirst($type ?? 'User') }}</h5>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.create', $type) }}"
                class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
                title="Tambah {{ ucfirst($type ?? 'User') }}">
                <i class="fas fa-plus"></i>
            </a>
            <a href="{{ route('admin.users.export', array_merge(['type' => $type], request()->query())) }}"
                class="btn btn-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
                title="Export Data">
                <i class="fas fa-download"></i>
            </a>
            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>

    <!-- Collapse Form -->
    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('admin.users.index', $type) }}">
            <div class=" my-2">
                <div class="row g-2 align-items-end">

                    @if ($type === 'student')
                        <div class="col-md-4">
                            <label for="nim" class="form-label mb-1">NIM</label>
                            <input type="text" class="form-control " name="nim" value="{{ request('nim') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="semester_id" class="form-label mb-1">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-select">
                                <option value="">-- Semua Mahasiswa --</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}"
                                        {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->semester_name }} -
                                        {{ $semester->academicYear->year_name }}
                                        @if ($activeSemester && $semester->id == $activeSemester->id)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    @elseif($type === 'lecturer')
                        <div class="col-md-4">
                            <label for="nidn" class="form-label mb-1">NIDN</label>
                            <input type="text" class="form-control " name="nidn" value="{{ request('nidn') }}">
                        </div>
                    @endif

                    <div class="col-md-4">
                        <label for="name" class="form-label mb-1">Nama</label>
                        <input type="text" class="form-control " name="name" value="{{ request('name') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="email" class="form-label mb-1">Email</label>
                        <input type="text" class="form-control " name="email" value="{{ request('email') }}">
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('admin.users.index', ['type' => $type, 'reset' => true]) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @foreach ($users as $user)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-auto ">
                <div class="card-body p-3 pb-0">
                    <div class="row">
                        <div class="d-flex flex-column h-100">
                            {{-- Nama --}}
                            <h5 class="font-weight-bolder mb-1">{{ $user->name }}</h5>
                            <p class="mb-1 text-secondary">
                                <i class="fas fa-envelope me-2"></i>
                                {{ $user->email }}
                            </p>

                            {{-- Role Info --}}
                            @if ($user->hasRole('student'))
                                <p class="mb-1">
                                    <i class="fas fa-id-card me-2"></i>
                                    NIM: {{ $user->student->nim ?? '-' }}
                                </p>
                            @elseif($user->hasRole('lecturer'))
                                <p class="mb-1">
                                    <i class="fas fa-id-badge me-2"></i>
                                    NIDN: {{ $user->lecturer->nidn ?? '-' }}
                                </p>
                            @else
                                <p class="mb-1">
                                    <i class="fas fa-user me-2"></i>
                                    Role: {{ ucfirst($user->roles->first()->name ?? '-') }}
                                </p>
                            @endif

                            {{-- Tombol Aksi --}}
                            <div class="my-auto pt-2">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.users.edit', [$type, $user->id]) }}"
                                        class="btn btn-sm bg-gradient-primary flex-fill" title="Edit">
                                        <i class="fa-solid fa-pen me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('admin.users.show', [$type, $user->id]) }}"
                                        class="btn btn-sm bg-gradient-secondary flex-fill" title="Info">
                                        <i class="fas fa-info-circle me-1"></i> Info
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$users" />
    </div>
@endsection
@push('dashboard')
