@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="mb-0">List Stase</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <!-- Tombol toggle collapse -->
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('stase.create') }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                        + Stase
                    </a>
                </div>
            </div>

            <!-- Collapse Form -->
            <div class="collapse" id="filterCollapse">
                <form method="GET" action="{{ route('stase.index') }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-12">
                                <label for="name" class="form-label mb-1">Stase</label>
                                <input type="text" class="form-control " name="name" value="{{ request('name') }}">
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('stase.index') }}" class="btn btn-light btn-sm">Reset</a>
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
                                    Code</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Nama</th>
                                <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($stases as $stase)
                            <tr>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $stase->code }}
                                </td>
                                <td class="align-middle text-center text-sm font-weight-bold">
                                    {{ $stase->name }}
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('stase.edit', $stase->id) }}"
                                        class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('stase.destroy', $stase->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn bg-gradient-danger m-1 p-2 px-3" title="Delete"
                                            onclick="return confirm('Apakah anda yakin ingin menghapus stase ini?');">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$stases" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection