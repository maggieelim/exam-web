@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="mb-0">Logbook Mahasiswa -
                        @if($status == 'pending')
                        Waiting for Approval
                        @elseif($status == 'approved')
                        Approved
                        @else
                        Denied
                        @endif
                    </h5>
                </div>
                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <!-- Tombol toggle collapse -->
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('logbook.index', ['status' => $status]) }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label for="name" class="form-label mb-1">Nama Mahasiswa</label>
                                <input type="text" class="form-control" name="name" value="{{ request('name') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nim" class="form-label mb-1">NIM</label>
                                <input type="text" class="form-control" name="nim" value="{{ request('nim') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="hospital" class="form-label mb-1">Rumah Sakit</label>
                                <input type="text" class="form-control" name="hospital"
                                    value="{{ request('hospital') }}">
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('logbook.index', ['status' => $status]) }}"
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
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Student</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Stase</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Activity
                                </th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Date</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Status</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($logbooks as $logbook)
                            <tr>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $logbook->studentKoas->student->user->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $logbook->studentKoas->hospitalRotation->clinicalRotation->name }} <br> {{
                                    $logbook->studentKoas->hospitalRotation->hospital->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $logbook->activityKoas->name }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $logbook->date->format('d M Y') }}
                                </td>
                                <td class="align-middle text-center font-weight-bold">
                                    <span class="badge 
                                        @if($logbook->status == 'approved') bg-success
                                        @elseif($logbook->status == 'rejected') bg-danger
                                        @else bg-warning @endif">
                                        {{ ucfirst($logbook->status) }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('logbook.edit', ['status' => $status, 'logbook' => $logbook->id]) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    Tidak ada data logbook dengan status ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $logbooks->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection