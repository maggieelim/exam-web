@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <h5 class="mb-0">Detail {{ ucfirst($type) }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route(session('context').'.admin.users.index', $type) }}"
                        class="btn btn-sm btn-secondary">Back</a>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                        data-bs-target="#deleteModal">
                        Delete
                    </button>
                </div>
            </div>

            <div class="card-body px-4 pt-3 pb-3">
                <div class="row">
                    <!-- Data Umum User -->
                    <div class="col-md-4 mb-3">
                        <p><strong>Nama:</strong> {{ $user->name }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p><strong>Gender:</strong> {{ $user->gender }}</p>
                    </div>

                    <!-- Data Spesifik Student -->
                    @if ($type === 'student' && $user->student)
                    @include('admin.users.partials.student-details', ['student' => $user->student])
                    @endif

                    <!-- Data Spesifik Lecturer -->
                    @if ($type === 'lecturer' && $user->lecturer)
                    @include('admin.users.partials.lecturer-details', [
                    'lecturer' => $user->lecturer,
                    ])
                    @endif

                    <!-- Tabel Courses (Common untuk kedua tipe) -->
                    {{-- ================= STUDENT ================= --}}
                    @if ($type === 'student' && $user->student)

                    {{-- STUDENT PSPD → Kepaniteraan --}}
                    @if ($user->student->type === 'PSPD')

                    @if (count($kepaniteraan) > 0)
                    @include('admin.users.partials.kepaniteraan-table', [
                    'kepaniteraan' => $kepaniteraan
                    ])
                    @else
                    <div class="col-12">
                        <p class="text-muted text-center">Belum ada data kepaniteraan</p>
                    </div>
                    @endif

                    {{-- STUDENT NON PSPD → Courses --}}
                    @else
                    @if (count($courses) > 0)
                    @include('admin.users.partials.courses-table', [
                    'courses' => $courses,
                    'type' => $type,
                    ])
                    @else
                    <div class="col-12">
                        <p class="text-muted text-center">Tidak ada data courses</p>
                    </div>
                    @endif
                    @endif

                    @endif

                    {{-- ================= LECTURER ================= --}}
                    @if ($type === 'lecturer' && count($courses) > 0)
                    @include('admin.users.partials.courses-table', [
                    'courses' => $courses,
                    'type' => $type,
                    ])
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete -->
@include('partials.modals.delete-user', [
'user' => $user,
'type' => $type,
])
@endsection