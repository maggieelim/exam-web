@extends('layouts.user_type.auth')

@section('content')
<div class="col-12">
    <div class="card mb-4">
        <div class="card-header pb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start gap-2">
            <div class="d-flex flex-column">
                <h5 class="mb-0">Tutor Blok {{ $course->name }}</h5>
                <h6 class="mb-0">Kelompok {{ $kel }}</h6>
            </div>

            <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                <a href="{{ route('tutors.download', ['course' => $course->id, 'kelompok' => $kel, 'pemicus' => json_encode($pemicu)]) }}"
                    class="btn btn-success btn-sm" style="white-space: nowrap;">
                    <i class="fas fa-file-excel"></i> Export
                </a>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <div class="collapse" id="filterCollapse">
            <form method="GET" action="{{ route('tutors.detail', ['course' => $course->id, 'kelompok' => $kel]) }}">
                <div class="mx-3 my-2 py-2">
                    <div class="row g-2">
                        <input type="hidden" id="pemicu" name="pemicu" value="{{ $pemicusJson }}">
                        <div class="col-md-12">
                            <label for="search" class="form-label mb-1">NIM/Name</label>
                            <input type="text" name="search" id="search" class="form-control"
                                placeholder="Cari NIM/Name" value="{{ request('search') }}">
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('tutors.detail', ['course' => $course->id, 'kelompok' => $kel, 'pemicu' => $pemicusJson]) }}"
                                class="btn btn-light btn-sm">
                                Reset
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>


        <div class="card-body px-0 pt-0 pb-2">
            {{-- ================= DESKTOP TABLE (md ke atas) ================= --}}
            <div class="table-responsive p-0 d-none d-md-block">
                <table class="table align-items-center mb-0 text-wrap">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">NIM</th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder ">Name</th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Nilai</th>
                            <th class="text-uppercase text-dark text-sm font-weight-bolder text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($students as $studentId => $stu)
                        @php
                        $first = $stu->first();
                        $pemicus = $stu
                        ->pluck('pemicuDetail.teachingSchedule.pemicu_ke')
                        ->unique()
                        ->sort()
                        ->values();
                        $allPemicuDetailIds = $stu->pluck('pemicu_detail_id')->toArray();
                        @endphp

                        <tr>
                            <td class="text-center text-sm">{{ $first->courseStudent->student->nim }}</td>

                            <td class="text-wrap text-sm">
                                {{ $first->courseStudent->student->user->name }}
                            </td>

                            <td class="text-center text-sm">
                                {{ $stu->sum('total_score') }}
                            </td>

                            <td class="text-center d-flex justify-content-center flex-wrap">
                                @foreach ($pemicus as $index => $pemicuKe)
                                @php
                                $pemDetail = $stu->firstWhere(
                                'pemicuDetail.teachingSchedule.pemicu_ke',
                                $pemicuKe,
                                );

                                // default aman
                                $sessionId = null;
                                $isChecked = false;
                                $isExpired = true;

                                if ($pemDetail && $pemDetail->pemicuDetail?->teachingSchedule) {
                                $scheduleId = $pemDetail->pemicuDetail->teaching_schedule_id;

                                $sessionId = $attendanceSessions[$scheduleId]->id ?? null;

                                $isChecked =
                                isset($existingAttendance[$studentId]) &&
                                $sessionId &&
                                $existingAttendance[$studentId]
                                ->pluck('attendance_session_id')
                                ->contains($sessionId);

                                $schedule =
                                $pemDetail->pemicuDetail->teachingSchedule->scheduled_date;

                                if ($schedule) {
                                $isExpired = \Carbon\Carbon::now()->greaterThan(
                                \Carbon\Carbon::parse($schedule)->addHours(48),
                                );
                                }
                                }
                                @endphp

                                <div class="d-flex gap-2 mx-2">
                                    <input type="checkbox" class="attendance-checkbox m-1"
                                        data-student="{{ $studentId }}" data-session="{{ $sessionId }}"
                                        @checked($isChecked) @disabled($isExpired || !$sessionId)>

                                    <a href="{{ route('tutors.edit', [
                                                    'pemicu' => $pemDetail->pemicu_detail_id,
                                                    'pemicus' => json_encode($allPemicuDetailIds),
                                                    'student' => $studentId,
                                                ]) }}" class="btn btn-info discussion-btn m-1 p-2 
                                        {{ $isExpired ? 'disabled text-white' : '' }} 
                                         {{ !$isChecked ? 'disabled text-white' : '' }}">
                                        Diskusi {{ substr($pemicuKe, -1) }}
                                    </a>
                                </div>
                                @endforeach
                            </td>

                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Tidak ada Tutor yang ditemukan</p>
                                    <a href="{{ 'tutors/' . $kel }}" class="btn btn-sm btn-outline-primary">
                                        Reset Filter
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- ================= MOBILE CARD VIEW (sm ke bawah) ================= --}}
    <div class="d-block d-md-none">

        @forelse ($students as $studentId => $stu)
        @php
        $first = $stu->first();
        $pemicus = $stu->pluck('pemicuDetail.teachingSchedule.pemicu_ke')->unique()->sort()->values();
        $allPemicuDetailIds = $stu->pluck('pemicu_detail_id')->toArray();
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-body p-2 m-2 mb-0">

                {{-- Nama --}}
                <h6 class="mb-1">
                    <strong>{{ $first->courseStudent->student->user->name }}</strong>
                </h6>

                {{-- Detail --}}
                <p class="text-muted mb-2">
                    NIM: {{ $first->courseStudent->student->nim }} <br>
                    Nilai: <strong>{{ $stu->sum('total_score') }}</strong>
                </p>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    @foreach ($pemicus as $index => $pemicuKe)
                    @php
                    $pemDetail = $stu->firstWhere('pemicuDetail.teachingSchedule.pemicu_ke', $pemicuKe);

                    $sessionId = null;
                    $isChecked = false;
                    $isExpired = true;

                    if ($pemDetail && $pemDetail->pemicuDetail?->teachingSchedule) {
                    $scheduleId = $pemDetail->pemicuDetail->teaching_schedule_id;

                    $sessionId = $attendanceSessions[$scheduleId]->id ?? null;

                    $isChecked =
                    isset($existingAttendance[$studentId]) &&
                    $sessionId &&
                    $existingAttendance[$studentId]
                    ->pluck('attendance_session_id')
                    ->contains($sessionId);

                    $schedule = $pemDetail->pemicuDetail->teachingSchedule->scheduled_date;

                    if ($schedule) {
                    $isExpired = \Carbon\Carbon::now()->greaterThan(
                    \Carbon\Carbon::parse($schedule)->addHours(48),
                    );
                    }
                    }
                    @endphp

                    <div class="pemicu-wrapper d-flex align-items-center gap-2">
                        <input type="checkbox" class="attendance-checkbox m-1" data-student="{{ $studentId }}"
                            data-session="{{ $sessionId }}" @checked($isChecked) @disabled($isExpired || !$sessionId)>
                        <a href="{{ route('tutors.edit', [
                                        'pemicu' => $pemDetail->pemicu_detail_id,
                                        'pemicus' => json_encode($allPemicuDetailIds),
                                        'student' => $studentId,
                                    ]) }}" class="btn btn-info mx-2 flex-fill discussion-btn 
                            {{ !$isChecked ? 'disabled text-white' : '' }} 
                             {{ $isExpired ? 'disabled text-white' : '' }}">
                            Diskusi {{ substr($pemicuKe, -1) }}
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @empty
        <div class="text-center py-4">
            <div class="text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Tidak ada Tutor yang ditemukan</p>
                <a href="{{ 'tutors/' . $kel }}" class="btn btn-sm btn-outline-primary">
                    Reset Filter
                </a>
            </div>
        </div>
        @endforelse

    </div>

</div>

<script>
    document.querySelectorAll('.attendance-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const wrapper = this.closest('.d-flex');
                const discussionBtn = wrapper.querySelector('.discussion-btn');
                if (this.checked) {
                    discussionBtn.classList.remove('disabled');
                } else {
                    discussionBtn.classList.add('disabled');
                }
                const payload = {
                    attendance_session_id: this.dataset.session,
                    course_student_id: this.dataset.student,
                    checked: this.checked
                };

                fetch("{{ route('tutors.AttendanceAjax') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Server error');
                        return res.json();
                    })
                    .then(data => {
                        console.log('✅ Attendance berhasil disimpan', {
                            response: data,
                            payload: payload
                        });
                    })
                    .catch(err => {
                        console.error('❌ Gagal menyimpan attendance', err, payload);
                        alert('Gagal menyimpan kehadiran');
                        this.checked = !this.checked;
                    });
            });
        });
</script>
@endsection


@push('dashboard')
<style>
    /* Font lebih kecil di mobile */
    @media (max-width: 576px) {
        table.table {
            font-size: 12px;
        }

        table.table thead {
            display: none;
            /* hide header in mobile */
        }

        table.table tbody tr {
            display: block;
            margin-bottom: 12px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
        }

        table.table tbody td {
            display: flex;
            justify-content: space-between;
            text-align: left !important;
            padding: 6px 10px;
        }

        table.table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #555;
        }

        /* Action buttons wrap nicely */
        td[data-label="Action"] {
            display: block !important;
            text-align: center !important;
        }
    }
</style>
@endpush