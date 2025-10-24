@extends('layouts.user_type.auth')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div
                    class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                    <div>
                        <h5 class="mb-0">Daftar Tahun Akademik</h5>
                    </div>
                    <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">

                        <a href="{{ route('admin.semester.create') }}" class="btn btn-primary btn-sm"
                            style="white-space: nowrap;">
                            + New Academic Year
                        </a>
                    </div>
                </div>



                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Semester
                                    </th>
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Tahun Ajaran
                                    </th>
                                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($semesters as $semester)
                                    <tr>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold">{{ $semester->semester_name }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span
                                                class="text-sm font-weight-bold">{{ $semester->academicYear->year_name }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('admin.semester.edit', $semester->id) }}"
                                                class="btn bg-gradient-primary m-1 p-2 px-3" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="{{ route('admin.semester.show', $semester->id) }}"
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
                            <x-pagination :paginator="$semesters" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('dashboard')
