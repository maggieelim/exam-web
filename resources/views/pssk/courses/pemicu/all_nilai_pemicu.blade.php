<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        .compact-table {
            border-collapse: collapse;
            width: 100%;
        }

        .compact-table td,
        .compact-table th {
            border: 1px solid #000;
            padding: 8px;
        }

        .text-center {
            text-align: center;
        }

        .text-sm {
            font-size: 12px;
        }

        .group-header {
            font-weight: bold;
            background: #d9d9d9;
        }
    </style>
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
            @php
            $globalRowNumber = 3; // Mulai dari baris 3 karena baris 1-2 adalah header
            @endphp

            @foreach ($groupedStudents as $kelompok => $students)
            @php
            $colspan = 2 + ($preGroup * 2); // NIM + Nama + (preGroup * 2 kolom)
            $globalRowNumber++; // Baris untuk header kelompok
            @endphp

            <!-- Baris Header Kelompok -->
            <tr>
                <td class="group-header" colspan="{{ $colspan }}" style="font-weight: bold; background: #d9d9d9;">
                    Kelompok: {{ $kelompok }} (Jumlah = {{ $students->count() }} Siswa)
                </td>
            </tr>

            @foreach ($students as $cs)
            @php
            $studentScores = $scores[$cs->id] ?? collect();
            $lecturerName = $groupLecturer[$cs->kelompok] ?? '-';
            $colIndex = 3; // Mulai dari kolom C (karena A=NIM, B=Nama)
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

                // convert angka ke huruf kolom Excel
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                @endphp

                <td class="text-center text-sm">{{ $total }}</td>
                <td class="text-center text-sm">
                    =ROUND({{ $columnLetter }}{{ $globalRowNumber }}/24*100,2)
                </td>

                @php
                $colIndex += 2; // karena tiap pemicu ada 2 kolom (Total & Nilai)
                @endphp
                @endforeach
            </tr>
            @php
            $globalRowNumber++; // Increment untuk baris berikutnya
            @endphp
            @endforeach
            @endforeach
        </tbody>
    </table>
</body>

</html>