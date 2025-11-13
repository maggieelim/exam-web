@extends('layouts.user_type.auth')

@section('content')
    <div class="card">
        <div class="card-header mb-0 pb-0">
            <h5 class="text-uppercase">Grup tutor, skill lab & praktikum</h5>

            <div class="row">
                <div class = "col-md-4 col-6">
                    <p><strong>Tahun Ajaran:</strong> {{ $semester->academicYear->year_name }}</p>
                </div>
                <div class = "col-md-4 col-6">
                    <p><strong>Semester:</strong> {{ $semester->semester_name }}</p>
                </div>
                <div class = "col-md-4 col-6">
                    <p><strong>Blok:</strong> {{ $course->name }}</p>
                </div>
                <div class = "col-md-4 col-6">
                    <p><strong>Total Mahasiswa:</strong> {{ $studentData->students->count() }}
                    </p>
                </div>
                <div class = "col-md-4 col-6">
                    <p><strong>Jumlah per-Kelompok:</strong> {{ $jumlahPerKelompok ?? '0' }}</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-secondary"
                    href="{{ route('courses.edit', $course->slug) }}?semester_id={{ $semester->id }}&tab=siswa">
                    Back
                </a>
            </div>
        </div>

        <div class="card-body mt-0 pt-0">
            <form id="groupForm" action="{{ route('admin.courses.updateGroup', $course->slug) }}" method="POST">
                @csrf
                <div class="table-responsive p-0">

                    <input type="hidden" name="semester_id" value="{{ $semesterId }}">

                    <table class="compact-table table-bordered">
                        <thead class="text-center align-middle">
                            <tr>
                                <th rowspan="3">Kelompok</th>
                                <th colspan="4">Group SKILL LAB</th>
                                <th colspan="9">Group PRAKTIKUM</th>
                            </tr>
                            <tr>
                                <th rowspan="2">A</th>
                                <th rowspan="2">B</th>
                                <th rowspan="2">C</th>
                                <th rowspan="2">D</th>
                                <th colspan="4">TIPE 1</th>
                                <th colspan="3">TIPE 2</th>
                                <th colspan="2">TIPE 3</th>
                            </tr>
                            <tr>
                                <th>A1</th>
                                <th>A2</th>
                                <th>B1</th>
                                <th>B2</th>
                                <th>A</th>
                                <th>B</th>
                                <th>C</th>
                                <th>1</th>
                                <th>2</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($studentData->groupedStudents as $kelompok => $students)
                                <tr>
                                    <td>
                                        Kelompok {{ $kelompok }}
                                    </td>
                                    @foreach (['A', 'B', 'C', 'D'] as $group)
                                        <td class="text-center">
                                            @php
                                                $isChecked = $skillslabDetails
                                                    ->where('kelompok_num', $kelompok)
                                                    ->where('group_code', $group)
                                                    ->isNotEmpty();
                                            @endphp
                                            <input type="checkbox" name="selected_groups[]"
                                                value="{{ $group }}_{{ $kelompok }}"
                                                class="group-checkbox input-bg" data-kelompok="{{ $kelompok }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                    @foreach (['A1', 'A2', 'B1', 'B2'] as $group)
                                        @php
                                            $isChecked = $practicumDetails
                                                ->where('kelompok_num', $kelompok)
                                                ->where('tipe', 'tipe1')
                                                ->where('group_code', $group)
                                                ->isNotEmpty();
                                        @endphp
                                        <td class="text-center soft-info">
                                            <input type="checkbox"
                                                name="selected_practicum_groups[{{ $kelompok }}][tipe1][{{ $group }}]"
                                                value="1" class="group-checkbox input-bg"
                                                data-original="{{ $isChecked ? 1 : 0 }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                    @foreach (['A', 'B', 'C'] as $group)
                                        @php
                                            $isChecked = $practicumDetails
                                                ->where('kelompok_num', $kelompok)
                                                ->where('tipe', 'tipe2')
                                                ->where('group_code', $group)
                                                ->isNotEmpty();
                                        @endphp
                                        <td class="text-center soft-info1">
                                            <input type="checkbox"
                                                name="selected_practicum_groups[{{ $kelompok }}][tipe2][{{ $group }}]"
                                                value="1" class="group-checkbox input-bg"
                                                data-original="{{ $isChecked ? 1 : 0 }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                    @foreach (['1', '2'] as $group)
                                        @php
                                            $isChecked = $practicumDetails
                                                ->where('kelompok_num', $kelompok)
                                                ->where('tipe', 'tipe3')
                                                ->where('group_code', $group)
                                                ->isNotEmpty();
                                        @endphp
                                        <td class="text-center soft-info2">
                                            <input type="checkbox"
                                                name="selected_practicum_groups[{{ $kelompok }}][tipe3][{{ $group }}]"
                                                value="1" class="group-checkbox input-bg"
                                                data-original="{{ $isChecked ? 1 : 0 }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
