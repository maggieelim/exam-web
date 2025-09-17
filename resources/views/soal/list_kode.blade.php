@extends('layouts.user_type.auth')

@section('content')
<div class="container">
    <h2>Daftar Kode Soal</h2>

    <div class="mb-3">
        <a href="{{ route('soal.upload') }}" class="btn btn-success">
            + Upload Soal
        </a>
    </div>

    <div class="row">
        @foreach($kodes as $kode)
            <div class="col-md-4 mb-3">
                <a href="{{ route('soal.showByKode', $kode->kode_soal) }}" class="btn btn-primary w-100">
                    {{ $kode->kode_soal }} <br>
                    <small>{{ $kode->nama_blok }}</small>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
