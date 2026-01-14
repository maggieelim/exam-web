<table class="compact-table table-bordered">
    <thead class="text-center align-middle">
        <tr>
            <th rowspan="2">NIM</th>
            <th rowspan="2">Nama</th>
            <th colspan="3">Diskusi 1</th>
            <th colspan="5">Diskusi 2</th>
            <th rowspan="2">Nilai</th>
            <th rowspan="2">%</th>
            <th rowspan="2">Dosen</th>
        </tr>

        <tr>
            <th class="text-wrap">Disiplin</th>
            <th class="text-wrap">Keaktifan</th>
            <th class="text-wrap">Berpikir Kritis</th>

            <th class="text-wrap">Disiplin</th>
            <th class="text-wrap">Keaktifan</th>
            <th class="text-wrap">Berpikir Kritis</th>
            <th class="text-wrap">Informasi Relevan</th>
            <th class="text-wrap">Analisis Sintesis</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($groupedStudents as $kelompok => $students)
        <tr>
            <td colspan="13" style="font-weight: bold; background: #d9d9d9;">
                Kelompok: {{ $kelompok }} ({{ $students->count() }} Siswa)
            </td>
        </tr>

        @foreach ($students as $cs)
        @php
        $studentScores = $scores[$cs->id] ?? collect();

        $scoreD1 = $studentScores->where('teaching_schedule_id', $id1)->first();
        $scoreD2 = $studentScores->where('teaching_schedule_id', $id2)->first();

        $total = ($scoreD1->total_score ?? 0) + ($scoreD2->total_score ?? 0);
        $percent = 24 > 0 ? ($total / 24) * 100 : 0;

        $lecturerName = $groupLecturer[$cs->kelompok] ?? '-';
        @endphp

        <tr>
            <td>{{ $cs->student->nim }}</td>
            <td>{{ ucwords(strtolower($cs->student->user->name)) }}</td>

            {{-- Diskusi 1 --}}
            <td>{{ $scoreD1->disiplin ?? '-' }}</td>
            <td>{{ $scoreD1->keaktifan ?? '-' }}</td>
            <td>{{ $scoreD1->berpikir_kritis ?? '-' }}</td>

            {{-- Diskusi 2 --}}
            <td>{{ $scoreD2->disiplin ?? '-' }}</td>
            <td>{{ $scoreD2->keaktifan ?? '-' }}</td>
            <td>{{ $scoreD2->berpikir_kritis ?? '-' }}</td>
            <td>{{ $scoreD2->info_baru ?? '-' }}</td>
            <td>{{ $scoreD2->analisis_rumusan ?? '-' }}</td>

            <td>{{ $total }}</td>
            <td>{{ number_format($percent, 2) }}%</td>
            <td>{{ $lecturerName }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>