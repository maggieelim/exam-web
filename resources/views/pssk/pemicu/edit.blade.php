{{-- resources/views/pemicu/edit.blade.php --}}
@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form
            action="{{ route('tutors.update', [$score->pemicu_detail_id, $score->course_student_id, 'pemicus' => json_encode($pemicus)]) }}"
            method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <div class="text-center">
                        <h5 style="font-size: clamp(0.9rem, 3vw, 1.25rem);">
                            {{ $student->user->name }} ({{ $student->nim }})
                        </h5>

                        <h6 style="font-size: clamp(0.8rem, 2.5vw, 1.1rem);">
                            {{ $score->courseStudent->course->name }}
                            - {{ $teachingSchedule->pemicu_ke }}
                            - {{ \Carbon\Carbon::parse($teachingSchedule->scheduled_date)->translatedFormat('l, d F Y')
                            }}
                        </h6>
                    </div>
                </div>
                <div class="card-body py-0">
                    {{-- Gunakan component score picker --}}
                    @include('components.score-picker', [
                    'name' => 'disiplin',
                    'label' => 'Disiplin (Hadir tepat waktu dan fokus pada proses diskusi)',
                    'value' => $score->disiplin ?? 0
                    ])

                    @include('components.score-picker', [
                    'name' => 'keaktifan',
                    'label' => 'Keaktifan dan penyampaian pendapat',
                    'value' => $score->keaktifan ?? 0
                    ])

                    @include('components.score-picker', [
                    'name' => 'berpikir_kritis',
                    'label' => 'Berpikir Kritis',
                    'value' => $score->berpikir_kritis ?? 0
                    ])

                    @php
                    $secondDigit = substr($score->teachingSchedule->pemicu_ke, -1);
                    @endphp

                    @if ($secondDigit == 2)
                    @include('components.score-picker', [
                    'name' => 'info_baru',
                    'label' => 'Membawa informasi baru yang relevan dari berbagai sumber',
                    'value' => $score->info_baru ?? 0
                    ])

                    @include('components.score-picker', [
                    'name' => 'analisis_rumusan',
                    'label' => 'Mampu menganalisis informasi dan merumuskan kembali/sintesis',
                    'value' => $score->analisis_rumusan ?? 0
                    ])
                    @endif
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection