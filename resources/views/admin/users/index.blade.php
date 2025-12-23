@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="mb-0">List {{ ucfirst($type ?? 'User') }}</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <!-- Tombol toggle collapse -->
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.export', array_merge(['type' => $type], request()->query())) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    <a href="{{ route('admin.users.create', $type) }}" class="btn btn-primary btn-sm"
                        style="white-space: nowrap;">
                        + New {{ ucfirst($type ?? 'User') }}
                    </a>
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('admin.users.index', $type) }}">
                    <div class="mx-3 my-2 py-2">
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
                                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                        }}>
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

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        @php
                        // Ambil semua parameter filter aktif, kecuali sort, dir, dan pagination
                        $filters = request()->except(['sort', 'dir', 'page']);
                        @endphp

                        <thead>
                            <tr>
                                @if ($type === 'student')
                                <x-sortable-th label="NIM" field="nim" :sort="$sort" :dir="$dir" />
                                @endif
                                <x-sortable-th label="Name" field="name" :sort="$sort" :dir="$dir" />
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Email</th>
                                @if ($type !== 'student')
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Role</th>
                                @endif
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                {{-- NIM / NIDN hanya jika student/lecturer --}}
                                @if ($user->hasRole('student'))
                                <td class="align-middle text-center">
                                    <span class="text-sm font-weight-bold">{{ $user->student->nim ?? '-' }}</span>
                                </td>
                                @endif
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $user->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $user->email }}
                                </td>
                                @if ($type === 'lecturer' || $type === 'admin')
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{$user->roles->pluck('name')->map('ucfirst')->implode(', ') ?? '-' }}
                                </td>
                                @endif
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.users.edit', [$type, $user->id]) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="{{ route('admin.users.show', [$type, $user->id]) }}"
                                        class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$users" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('dashboard')