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
                    <form id="nilaiForm" action="{{ route('saveNilai') }}" method="POST">
                        @csrf
                        <table class="compact-table table-bordered">
                            <thead class="text-center align-middle">
                                <tr>
                                    <th rowspan="2">NIM</th>
                                    <th style="width: 50px !important; white-space: normal !important; word-wrap: break-word !important;"
                                        rowspan="2">Nama</th>
                                    <th colspan="3">Diskusi 1</th>
                                    <th colspan="5">Diskusi 2</th>
                                    <th rowspan="2">Total</th>
                                    <th rowspan="2">Nilai</th>
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

                                $dosenD1 = $groupLecturer[$cs->kelompok][$id1] ?? '-';
                                $dosenD2 = $groupLecturer[$cs->kelompok][$id2] ?? '-';
                                @endphp


                                <tr class="collapse show" id="group-{{ $kelompok }}">
                                    <td class="text-center text-sm">{{ $cs->student->nim }}</td>

                                    <td style="width:300px; white-space:normal; word-wrap:break-word;">
                                        {{ucwords(strtolower($cs->student->user->name)) }}
                                    </td>

                                    {{-- Diskusi 1 --}}
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id1 }}][disiplin]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD1->disiplin ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id1 }}][keaktifan]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD1->keaktifan ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id1 }}][berpikir_kritis]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD1->berpikir_kritis ?? '' }}" min="0" max="3">
                                    </td>
                                    <input type="hidden" name="scores[{{ $cs->id }}][{{ $id1 }}][pemicu_detail_id]"
                                        value="{{ $scoreD1->pemicu_detail_id ?? $pemicuDetailMap[$id1] }}">

                                    {{-- Diskusi 2 --}}
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id2 }}][disiplin]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD2->disiplin ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id2 }}][keaktifan]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD2->keaktifan ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id2 }}][berpikir_kritis]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD2->berpikir_kritis ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id2 }}][info_baru]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD2->info_baru ?? '' }}" min="0" max="3">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $cs->id }}][{{ $id2 }}][analisis_rumusan]"
                                            class="form-control form-control-sm input-bg text-center"
                                            value="{{ $scoreD2->analisis_rumusan ?? '' }}" min="0" max="3">
                                    </td>
                                    <input type="hidden" name="scores[{{ $cs->id }}][{{ $id2 }}][pemicu_detail_id]"
                                        value="{{ $scoreD2->pemicu_detail_id ?? $pemicuDetailMap[$id2] }}">

                                    {{-- Total --}}
                                    <td class="text-center text-sm">{{ $total }}</td>
                                    <td class="text-center text-sm">{{ number_format($percent, 2) }}</td>

                                    {{-- Dosen --}}
                                    <td class="text-sm">
                                        @if ($dosenD1 && $dosenD2 && $dosenD1 !== $dosenD2)
                                        D1: {{ $dosenD1 }} <br>
                                        D2: {{ $dosenD2 }}
                                        @else
                                        {{ $dosenD1 ?? $dosenD2 ?? '-' }}
                                        @endif
                                    </td>
                                </tr>

                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Save
                            </button>
                        </div>
                    </form>
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