@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    <h5 class="mb-0">List Pertemuan Blok {{$course->name}}</h5>
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
                <form method="GET"
                    action="{{ route('attendances.report', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
                    <div class="mx-3 my-2 py-2">
                        <div class="row g-2">
                            <input type="hidden" name="semester_id" value="{{ $semesterId }}" />
                            <input type="hidden" name="course" value="{{ $course->slug }}" />
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">Start Date</label>
                                <input type="date" class="form-control form-control" name="start_date"
                                    value="{{ request('start_date') }}" placeholder="Tanggal Mulai">
                            </div>
                            <div class="col-md-6">
                                <label for="blok" class="form-label mb-1">End Date</label>
                                <input type="date" class="form-control form-control" name="end_date"
                                    value="{{ request('end_date') }}" placeholder="Tanggal Berakhir">
                            </div>
                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('attendances.report', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                    class="btn btn-light btn-sm">Reset</a>
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
                                    Kegiatan
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder  text-center">
                                    Tanggal
                                </th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                    Total Mahasiswa</th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center text-wrap">
                                    Total Hadir</th>
                                <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($attendances as $attendance)
                            <tr>
                                <td class="align-middle text-sm text-center">
                                    {{ $attendance->pemicu_label }} - {{ $attendance->diskusi_label }}
                                </td>
                                <td class="align-middle text-sm text-center">
                                    {{ $attendance->formatted_schedule }}
                                </td>

                                <td class="align-middle text-center text-sm">
                                    {{ $totalStudents }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ $attendance->present_count }} </td>
                                <td class="align-middle text-center text-sm">
                                    <a href="{{ route('attendances.report.show', ['course' => $course->slug, 'semester_id' => $semesterId, 'session' =>$attendance->id]) }}"
                                        class="btn bg-gradient-secondary m-1 p-2 px-3" title="Info">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada absensi yang ditemukan</p>
                                        <a href="{{ route('attendances.report', ['course' => $course->slug, 'semester_id' => $semesterId]) }}"
                                            class="btn btn-sm btn-outline-primary">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$attendances" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
@endpush