<table class="compact-table table-bordered">
    <thead class="text-center align-middle">
        <tr>
            <th rowspan="2">NIM</th>
            <th rowspan="2">Nama</th>
            <th colspan="3">Diskusi 1</th>
            <th colspan="5">Diskusi 2</th>
            <th rowspan="2">Total</th>
            <th rowspan="2">Nilai</th>
            <th colspan="2">Dosen</th>
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

            <th class="text-wrap">Diskusi 1</th>
            <th class="text-wrap">Diskusi 2</th>
        </tr>
    </thead>

    @php
    use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
    $globalRowNumber = 4;
    @endphp

    <tbody>
        @foreach ($groupedStudents as $kelompok => $students)
        <tr>
            <td style="font-weight: bold; background: #d9d9d9;">
                Kelompok: {{ $kelompok }} ({{ $students->count() }} Siswa)
            </td>
            @for ($i = 2; $i <= 14; $i++) <td style="background: #d9d9d9;">
                </td>
                @endfor
        </tr>

        @foreach ($students as $cs)
        @php
        $studentScores = $scores[$cs->id] ?? collect();

        $scoreD1 = $studentScores->where('teaching_schedule_id', $id1)->first();
        $scoreD2 = $studentScores->where('teaching_schedule_id', $id2)->first();

        // posisi kolom nilai (C sampai J)
        $startCol = 3;
        $endCol = 10;

        $startLetter = Coordinate::stringFromColumnIndex($startCol);
        $endLetter = Coordinate::stringFromColumnIndex($endCol);

        $currentRow = $globalRowNumber;

        $dosenD1 = $groupLecturer[$cs->kelompok][$id1] ?? '-';
        $dosenD2 = $groupLecturer[$cs->kelompok][$id2] ?? '-';
        @endphp

        <tr>
            <td>{{ $cs->student->nim }}</td>
            <td>{{ ucwords(strtolower($cs->student->user->name)) }}</td>

            {{-- Diskusi 1 --}}
            <td>{{ $scoreD1->disiplin ?? 0 }}</td>
            <td>{{ $scoreD1->keaktifan ?? 0 }}</td>
            <td>{{ $scoreD1->berpikir_kritis ?? 0 }}</td>

            {{-- Diskusi 2 --}}
            <td>{{ $scoreD2->disiplin ?? 0 }}</td>
            <td>{{ $scoreD2->keaktifan ?? 0 }}</td>
            <td>{{ $scoreD2->berpikir_kritis ?? 0 }}</td>
            <td>{{ $scoreD2->info_baru ?? 0 }}</td>
            <td>{{ $scoreD2->analisis_rumusan ?? 0 }}</td>

            {{-- TOTAL (Excel SUM) --}}
            <td class="text-center text-sm">
                =SUM({{ $startLetter }}{{ $currentRow }}:{{ $endLetter }}{{ $currentRow }})
            </td>

            {{-- NILAI (Excel ROUND) --}}
            <td class="text-center text-sm">
                =ROUND(K{{ $currentRow }}/24*100,2)
            </td>

            {{-- DOSEN --}}
            @if ($dosenD1 !== '-' && $dosenD2 !== '-' && $dosenD1 !== $dosenD2)
            <td class="text-wrap text-sm">{{ $dosenD1 }}</td>
            <td class="text-wrap text-sm">{{ $dosenD2 }}</td>
            @else
            <td colspan="2" class="text-wrap text-sm text-center">
                {{ $dosenD1 !== '-' ? $dosenD1 : $dosenD2 }}
            </td>
            @endif
        </tr>

        @php
        $globalRowNumber++;
        @endphp
        @endforeach
        @endforeach
    </tbody>
</table>