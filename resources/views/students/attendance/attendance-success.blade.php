@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">
    <div class="card text-center">
        <div class="card-body p-5">
            <div class="text-success mb-4">
                <i class="fas fa-check-circle fa-5x"></i>
            </div>
            <h3 class="text-success mb-3">Attendance Submitted Successfully!</h3>
            <p class="text-muted mb-4">
                Your attendance has been recorded for
                <strong>{{ $attendanceSession->course->name ?? 'N/A' }}</strong>
                at {{ \Carbon\Carbon::now()->format('H:i') }}
            </p>
            <a href="{{ route('student.attendance.index') }}" class="btn btn-primary">
                <i class="fas fa-times me-2"></i>
                Close Window
            </a>
        </div>
    </div>
</div>
@endsection