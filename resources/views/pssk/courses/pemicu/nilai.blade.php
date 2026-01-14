@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column">
                    <h5 class="mb-0">PEMICU {{ $preGroup }} BLOK {{$course->name}}</h5>
                </div>

                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <a href="{{ route('course.downloadNilai', ['id1' => $id1, 'id2' => $id2]) }}"
                        class="btn btn-success btn-sm" style="white-space: nowrap;">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
            </div>



            <div class="card-body">
                <div class="table-responsive p-0">
                    <table class="compact-table table-bordered">
                        <thead class="text-center align-middle">
                            <tr>
                                <th rowspan="2">NIM</th>
                                <th style="width: 50px !important; white-space: normal !important; word-wrap: break-word !important;"
                                    rowspan="2">Nama</th>
                                <th colspan="3">Diskusi 1</th>
                                <th colspan="5">Diskusi 2</th>
                                <th rowspan="2">Nilai</th>
                                <th rowspan="2">%</th>
                                <th rowspan="2">Dosen</th>
                            </tr>
                            <tr>
                                <th class="text-xxs">Disiplin</th>
                                <th class="text-xxs">Keaktifan</th>
                                <th class="text-wrap text-xxs">Berpikir Kritis</th>
                                <th class="text-xxs">Disiplin</th>
                                <th class="text-xxs">Keaktifan</th>
                                <th class="text-wrap text-xxs">Berpikir Kritis</th>
                                <th class="text-wrap text-xxs">Informasi Relevan</th>
                                <th class="text-wrap text-xxs">Analisis Sintesis</th>
                            </tr>

                        </thead>

                        <tbody>
                            @foreach ($groupedStudents as $kelompok => $students)
                            <tr>
                                <td class="group-header font-weight-bold" data-bs-toggle="collapse"
                                    data-bs-target="#group-{{ $kelompok }}" colspan="13">
                                    <i class="fas fa-caret-down collapse-icon"></i>
                                    Kelompok: {{ $kelompok }} (Jumlah = {{ $students->count() }} Siswa)
                                </td>
                            </tr>

                            @foreach ($students as $cs)

                            @php
                            $studentScores = $scores[$cs->id] ?? collect();

                            // Pecah berdasarkan teaching_schedule_id
                            $scoreD1 = $studentScores->where('teaching_schedule_id', $id1)->first();
                            $scoreD2 = $studentScores->where('teaching_schedule_id', $id2)->first();

                            // Total nilai
                            $total = ($scoreD1->total_score ?? 0) + ($scoreD2->total_score ?? 0);

                            $maxScore = 24;
                            $percent = $maxScore > 0 ? ($total / $maxScore) * 100 : 0;

                            $lecturerName = $groupLecturer[$cs->kelompok] ?? '-';
                            @endphp


                            <tr class="collapse show" id="group-{{ $kelompok }}">
                                <td class="text-center text-sm">{{ $cs->student->nim }}</td>

                                <td style="width:300px; white-space:normal; word-wrap:break-word;">
                                    {{ucwords(strtolower($cs->student->user->name)) }}
                                </td>

                                {{-- Diskusi 1 --}}
                                <td class="text-center text-sm">{{ $scoreD1->disiplin ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD1->keaktifan ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD1->berpikir_kritis ?? '-' }}</td>

                                {{-- Diskusi 2 --}}
                                <td class="text-center text-sm">{{ $scoreD2->disiplin ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD2->keaktifan ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD2->berpikir_kritis ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD2->info_baru ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $scoreD2->analisis_rumusan ?? '-' }}</td>

                                {{-- Total --}}
                                <td class="text-center text-sm">{{ $total }}</td>
                                <td class="text-center text-sm">{{ number_format($percent, 2) }}%</td>

                                {{-- Dosen --}}
                                <td>{{ $lecturerName }}</td>
                            </tr>

                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.group-header').forEach(header => {
                header.addEventListener('click', () => {
                    header.classList.toggle('collapsed');
                });
            });
        });  
</script>
@endsection