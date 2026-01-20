@extends('layouts.user_type.auth')

@section('content')
<div class="d-flex justify-content-between gap-2">
    <div>
        <h5 class="mb-0">Daftar Tahun Akademik</h5>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ (session('context').'.admin.semester.create') }}"
            class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"
            title="Tambah {{ ucfirst($type ?? 'User') }}">
            <i class="fas fa-plus"></i>
        </a>

    </div>
</div>


@foreach ($semesters as $semester)
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card h-auto ">
        <div class="card-body p-3 pb-0">
            <div class="row">
                <div class="d-flex flex-column h-100">
                    {{-- Nama --}}
                    <h5 class="font-weight-bolder mb-1">{{ $semester->semester_name }}
                        {{ $semester->academicYear->year_name }}
                    </h5>

                    {{-- Tombol Aksi --}}
                    <div class="my-auto pt-2">
                        <div class="d-flex gap-2">
                            <a href="{{ (session('context').'.admin.semester.edit', $semester->id) }}"
                                class="btn btn-sm bg-gradient-primary flex-fill" title="Edit">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </a>
                            <a href="{{ (session('context').'.admin.semester.show', $semester->id) }}"
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
    <x-pagination :paginator="$semesters" />
</div>
@endsection
@push('dashboard')