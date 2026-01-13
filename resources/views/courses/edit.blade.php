@extends('layouts.user_type.auth')

@section('content')
@php
// Definisikan tabs di controller atau sebagai view composer
$navigationTabs = [
'kelas' => 'KELAS',
'siswa' => 'SISWA',
'dosen' => 'DOSEN',
'praktikum' => 'PRAKTIKUM',
'pemicu' => 'PEMICU',
'pleno' => 'PLENO',
'skilllab' => 'SKILL LAB'
];
@endphp

<div class="col-12 mb-4">
    <div class="card">
        <div class="card-header pb-2">
            <h4 class="text-uppercase">PENJADWALAN PSSK - Blok {{ $course->name }}</h4>

            <ul class="nav nav-tabs card-header-tabs" id="courseTabs" role="tablist">
                @foreach($navigationTabs as $tabKey => $tabLabel)
                <li class="nav-item">
                    <a class="nav-link @if($activeTab == $tabKey) active @endif"
                        href="{{ request()->fullUrlWithQuery(['tab' => $tabKey]) }}">
                        {{ $tabLabel }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body px-4 pt-3 pb-2">
            {{-- Dynamic include with fallback --}}
            @if(View::exists("courses.tabs._$activeTab"))
            @include("courses.tabs._$activeTab")
            @else
            @include('courses.tabs._kelas')
            @endif
        </div>
    </div>
</div>
@endsection