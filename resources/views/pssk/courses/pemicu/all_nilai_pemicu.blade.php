<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
    <table class="compact-table table-bordered" border="1">
        <thead class="text-center align-middle">
            <tr>
                <th rowspan="2">NIM</th>
                <th rowspan="2">Nama</th>
                @foreach (range(1, $preGroup) as $index)
                <th colspan="2">Pemicu {{ $index }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach (range(1, $preGroup) as $index)
                <th>Total</th>
                <th>Nilai</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($groupedStudents as $kelompok => $students)
            <tr>
                @php
                $colspan = 3 + ($preGroup * 2);
                @endphp
                <td class="group-header" colspan="{{ $colspan }}" style="font-weight: bold; background: #d9d9d9;">
                    Kelompok: {{ $kelompok }} (Jumlah = {{ $students->count() }} Siswa)
                </td>
            </tr>

            @foreach ($students as $cs)
            @php
            $studentScores = $scores[$cs->id] ?? collect();
            $lecturerName = $groupLecturer[$cs->kelompok] ?? '-';
            @endphp

            <tr>
                <td class="text-center text-sm">{{ $cs->student->nim }}</td>
                <td style="width:300px; white-space:normal; word-wrap:break-word;">
                    {{ ucwords(strtolower($cs->student->user->name)) }}
                </td>

                {{-- Loop untuk setiap pemicu --}}
                @foreach ($pemicuGroups as $pemicuNumber => $scheduleIds)
                @php
                $id1 = $scheduleIds[0] ?? null;
                $id2 = $scheduleIds[1] ?? null;

                $scoreD1 = $studentScores->where('teaching_schedule_id', $id1)->first();
                $scoreD2 = $studentScores->where('teaching_schedule_id', $id2)->first();

                $total = ($scoreD1->total_score ?? 0) + ($scoreD2->total_score ?? 0);
                $maxScore = 24;
                $percent = $maxScore > 0 ? ($total / $maxScore) * 100 : 0;
                @endphp

                <td class="text-center text-sm">{{ $total }}</td>
                <td class="text-center text-sm">{{ number_format($percent, 2) }}</td>
                @endforeach

            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</body>

</html>