@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div
                class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-column">
                    <h5 class="mb-0">SEMUA PEMICU BLOK {{ $course->name }}</h5>
                </div>

                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                    <a href="{{ route('course.downloadAllPemicu', ['id'=>$course->id, 'semester'=>$semester]) }}"
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
                                <th rowspan="2">Nama</th> @foreach (range(1, $preGroup) as $index) <th colspan="2">
                                    Pemicu {{ $index }}</th> @endforeach
                            </tr>
                            <tr> @foreach (range(1, $preGroup) as $index) <th>Nilai</th>
                                <th>%</th> @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($groupedStudents as $kelompok => $students)
                            <tr>
                                <td class="group-header" data-bs-toggle="collapse"
                                    data-bs-target="#group-{{ $kelompok }}"
                                    colspan="{{ 2 + (count($pemicuGroups) * 10) + 1 }}">
                                    <i class="fas fa-caret-down collapse-icon"></i>
                                    Kelompok: {{ $kelompok }} (Jumlah = {{ $students->count() }} Siswa)
                                </td>
                            </tr>

                            @foreach ($students as $cs)
                            @php
                            $studentScores = $scores[$cs->id] ?? collect();
                            $lecturerName = $groupLecturer[$cs->kelompok] ?? '-';
                            @endphp

                            <tr class="collapse show" id="group-{{ $kelompok }}">
                                <td class="text-center text-sm">{{ $cs->student->nim }}</td>
                                <td style="width:300px; white-space:normal; word-wrap:break-word;">
                                    {{ ucwords(strtolower($cs->student->user->name)) }}
                                </td>

                                {{-- Loop untuk setiap pemicu --}}
                                @foreach ($pemicuGroups as $pemicuNumber => $scheduleIds)
                                @php
                                // Ambil schedule IDs untuk pemicu ini
                                $id1 = $scheduleIds[0] ?? null; // Diskusi 1
                                $id2 = $scheduleIds[1] ?? null; // Diskusi 2

                                // Ambil scores berdasarkan teaching_schedule_id
                                $scoreD1 = $studentScores->where('teaching_schedule_id', $id1)->first();
                                $scoreD2 = $studentScores->where('teaching_schedule_id', $id2)->first();

                                // Total nilai
                                $total = ($scoreD1->total_score ?? 0) + ($scoreD2->total_score ?? 0);
                                $maxScore = 24;
                                $percent = $maxScore > 0 ? ($total / $maxScore) * 100 : 0;
                                @endphp

                                {{-- Total --}}
                                <td class="text-center text-sm">{{ $total }}</td>
                                <td class="text-center text-sm">{{ number_format($percent, 2) }}%</td>
                                @endforeach
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

<style>
    .text-xxs {
        font-size: 0.7rem !important;
    }

    .compact-table th,
    .compact-table td {
        padding: 2px 4px !important;
        font-size: 0.8rem;
    }
</style>
@endsection