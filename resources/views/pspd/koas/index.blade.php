@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    <h5 class="mb-0">List Mahasiswa Koas</h5>
                    @if ($semesterId)
                    @php
                    $selectedSemester = $semesters->firstWhere('id', $semesterId);
                    @endphp
                    <x-semester-badge :semester="$selectedSemester" :activeSemester="$activeSemester" />
                    @endif
                </div>
                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('mahasiswa-koas.index') }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label for="name" class="form-label mb-1">Rumah Sakit</label>
                                <input type="text" class="form-control " name="name" value="{{ request('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="semester_id" class="form-label mb-1">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select">
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                        }}>
                                        {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                                        @if ($activeSemester && $semester->id == $activeSemester->id)
                                        (Aktif)
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('mahasiswa-koas.index') }}" class="btn btn-light btn-sm">Reset</a>
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
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Rumah Sakit</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Stase</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Jumlah Peserta</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Start Date</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    End Date</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($students as $student)
                            <tr>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $student->student->user->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $student->student->nim }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $student->hospitalRotation->hospital->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $student->hospitalRotation->clinicalRotation->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $student->start_date->format('d M Y') }} - {{ $student->end_date->format('d M
                                    Y') }}
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('mahasiswa-koas.edit', $student->id) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="{{ route('mahasiswa-koas.show', $student->id) }}"
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
                        <x-pagination :paginator="$students" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection