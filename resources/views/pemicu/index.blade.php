@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column">
                    <h5 class="mb-0">Tutor</h5>
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
                <form method="GET" action="{{ 'tutors' }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="search" class="form-label mb-1">NIM/Name</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Cari NIM/Name" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label mb-1">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="present" {{ request('status')=='present' ? 'selected' : '' }}>Present
                                    </option>
                                    <option value="late" {{ request('status')=='late' ? 'selected' : '' }}>Late</option>
                                    <option value="absent" {{ request('status')=='absent' ? 'selected' : '' }}>Absent
                                    </option>

                                </select>
                            </div>
                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ 'tutors' }}" class="btn btn-light btn-sm">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 text-wrap">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-wrap text-center">
                                    Blok
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-wrap text-center">
                                    Pemicu Ke
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder  text-center">
                                    Kelompok
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">
                                    Jumlah Mahasiswa
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($tutors as $tutor)
                            <tr>
                                <td class="align-middle text-sm text-center">
                                    {{ $tutor['course']->name}}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    Pemicu {{ $tutor['pemicu'] }}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    {{ $tutor['kelompok']}}
                                </td>
                                <td class="align-middle text-center text-sm">
                                    {{ $tutor['student_count']}}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    <a href="{{ route('tutors.show', [
    'course' =>  $tutor['course']->id,
    'kelompok' => $tutor['kelompok'],
    'pemicu' => json_encode($tutor['pemicu_detail_ids'])
]) }}" class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-info-circle"></i>
                                    </a>

                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada Tutor yang ditemukan</p>
                                        <a href="{{ 'tutors' }}" class="btn btn-sm btn-outline-primary">Reset
                                            Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$tutors" />
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush