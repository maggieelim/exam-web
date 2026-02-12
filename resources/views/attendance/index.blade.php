@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body pb-2">
                <div class="mb-0 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-start
                gap-2">
                    <h5>
                        @if ($lecturer)
                        {{ $lecturer->user->name }}'s Schedule
                        @endif
                    </h5>
                    @if ($lecturer->user->id !== auth()->id())
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
                    @endif
                </div>
                <div id="calendar" style="max-width: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    /* Kalender bisa scroll horizontal jika space sempit */
    #calendar {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }

    .fc .fc-col-header-cell-cushion {
        color: #344767 !important
    }

    .fc .fc-toolbar-title {
        color: #344767 !important
    }

    .fc .fc-button-primary {
        background-color: rgb(141, 195, 231);
        border-color: rgb(141, 195, 231);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #344767;
        border-color: #344767;
    }

    .fc .fc-button-primary:disabled {
        background-color: #344767;
        border-color: #344767;
    }

    /* Toolbar tidak mepet dan tidak pecah */
    @media (max-width: 767px) {
        .fc-header-toolbar {
            flex-wrap: wrap !important;
            gap: 6px;
            justify-content: center;
        }

        .fc-toolbar-chunk {
            width: 100%;
            text-align: right;
        }

        /* Untuk mempersempit kolom jam jika layar sangat kecil */
        .fc-timegrid-slot-label {
            font-size: 5px !important;
        }

        .fc-timegrid-event {
            font-size: 11px;
        }

    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        if (!calendarEl) return;

        // Deteksi jika device mobile
        const isMobile = window.innerWidth <= 768;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: isMobile ? 'timeGridDay' :
            'timeGridWeek', // Day untuk mobile, Week untuk desktop
            nowIndicator: true,
            allDaySlot: false,
            slotMinTime: "07:00:00",
            slotMaxTime: "17:00:00",
            hiddenDays: [0, 6],
            events: {
                url: '{{ route('attendances.json') }}',
                extraParams: {
                    lecturer_id: '{{ request('lecturer_id') }}'
                }
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault(); // ⛔ cegah redirect
                info.jsEvent.stopPropagation();

                // optional: kalau mau aksi lain
                // console.log(info.event);
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: true
            },
            headerToolbar: {
                right: 'prev,next today',
                center: 'title',
                left: isMobile ? '' : 'dayGridMonth,timeGridWeek,timeGridDay' // Sesuaikan toolbar
            },
            views: {
                dayGridMonth: {
                    dayHeaderFormat: {
                        weekday: 'short',
                        day: 'numeric'
                    }
                },
                timeGridWeek: {
                    dayHeaderFormat: {
                        day: 'numeric',
                        weekday: 'long',
                    }
                }
            },
            height: 'auto'
        });

        calendar.render();
    });
</script>
@push('dashboard')
@endpush