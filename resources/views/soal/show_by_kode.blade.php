@extends('layouts.user_type.auth')

@section('content')
<div class="container">
    <h2 class="mb-3">Soal dengan Kode: {{ $kode }}</h2>
    <p><strong>Nama Blok:</strong> {{ $soals->first()->nama_blok ?? '-' }}</p>

    @forelse($soals as $index => $soal)
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">Soal {{ $index + 1 }}</h6>

                {{-- Badan Soal (opsional) --}}
                @if($soal->badan_soal)
                    <p><strong>{{ $soal->badan_soal }}</strong></p>
                @endif

                {{-- Kalimat Tanya --}}
                <p>{{ $soal->kalimat_tanya }}</p>

                {{-- Pilihan Jawaban --}}
                <ul class="list-unstyled">
                    <li>A. {{ $soal->opsi_a }}</li>
                    <li>B. {{ $soal->opsi_b }}</li>
                    <li>C. {{ $soal->opsi_c }}</li>
                    <li>D. {{ $soal->opsi_d }}</li>
                    @if($soal->opsi_e)
                        <li>E. {{ $soal->opsi_e }}</li>
                    @endif
                </ul>

                {{-- Jawaban --}}
                <p><strong>Jawaban Benar:</strong> {{ strtoupper($soal->jawaban) }}</p>
            </div>
        </div>
    @empty
        <div class="alert alert-warning">Belum ada soal untuk kode ini.</div>
    @endforelse
</div>
@endsection

