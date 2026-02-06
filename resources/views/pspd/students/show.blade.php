@extends('layouts.user_type.auth')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-0">Rekap Kegiatan per Activity</h5>
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Activity</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activitySummary as $item)
                <tr>
                    <td class="text-center">{{ $item->activityKoas->name }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        Belum ada activity yang disetujui
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection