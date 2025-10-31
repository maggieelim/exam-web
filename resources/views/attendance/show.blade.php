@extends('layouts.user_type.auth')

@section('content')
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-3">
                <h5 class="mb-1">Attendance Session: {{ $attendanceSession->absensi_code }}</h5>
                <div class="status-badge">
                    @if ($attendanceSession->isExpired())
                        <span class="badge bg-danger">Finished</span>
                    @elseif($attendanceSession->isActive())
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-warning">Not Started</span>
                    @endif
                </div>
            </div>
            <div class="card-body px-4 pt-2 pb-2">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="mb-3">QR Code for Attendance</h6>
                                <div id="qrCodeContainer">
                                    <div id="qrCode" class="mb-3"></div>
                                    @if ($attendanceSession->isExpired())
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Attendance session has ended. QR code is no longer available.
                                        </div>
                                    @elseif(!$attendanceSession->isActive())
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            QR code will be available when session starts.
                                        </div>
                                    @else
                                        <small class="text-muted">QR code refreshes every 60 seconds</small>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    <p class="small text-muted mb-1">Session Code:
                                        <strong>{{ $attendanceSession->absensi_code }}</strong>
                                    </p>
                                    <p class="small text-muted mb-1">Valid until:
                                        {{ \Carbon\Carbon::parse($attendanceSession->end_time)->format('M d, Y H:i') }}</p>
                                    <p class="small text-muted mb-1">Status:
                                        @if ($attendanceSession->isExpired())
                                            <span class="text-danger">Finished</span>
                                        @elseif($attendanceSession->isActive())
                                            <span class="text-success">Active</span>
                                        @else
                                            <span class="text-warning">Not Started</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Session details remain the same -->
                        <div class="card">
                            <div class="card-body">
                                <h6>Session Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Course:</strong></td>
                                        <td>{{ $attendanceSession->course->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Activity:</strong></td>
                                        <td>
                                            {{ $attendanceSession->activity->activity_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Start Time:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($attendanceSession->start_time)->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>End Time:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($attendanceSession->end_time)->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>
                                            Lat: {{ $attendanceSession->location_lat }}<br>
                                            Long: {{ $attendanceSession->location_long }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tolerance:</strong></td>
                                        <td>{{ $attendanceSession->tolerance_meter }} meters</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('dashboard')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

    <script>
        let refreshInterval;
        const attendanceCode = '{{ $attendanceSession->absensi_code }}';
        const isSessionActive = {{ $attendanceSession->isActive() ? 'true' : 'false' }};
        const isSessionExpired = {{ $attendanceSession->isExpired() ? 'true' : 'false' }};

        function generateQRCode() {
            // Don't generate QR if session has ended
            if (isSessionExpired) {
                clearInterval(refreshInterval);
                return;
            }

            // Don't generate QR if session hasn't started
            if (!isSessionActive) {
                clearInterval(refreshInterval);
                return;
            }

            fetch(`/attendance/${attendanceCode}/qr-code`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const qrCodeElement = document.getElementById('qrCode');
                    qrCodeElement.innerHTML = '';

                    if (data.expired) {
                        clearInterval(refreshInterval);
                        qrCodeElement.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${data.message}
                            </div>`;
                        document.querySelector('#qrCodeContainer small').textContent = 'Session ended';
                        return;
                    }

                    const qrData = data.url;

                    try {
                        const typeNumber = 0;
                        const errorCorrectionLevel = 'L';
                        const qr = qrcode(typeNumber, errorCorrectionLevel);
                        qr.addData(qrData);
                        qr.make();

                        const canvas = document.createElement('canvas');
                        const size = 250;
                        const cellSize = size / qr.getModuleCount();
                        const margin = 1;
                        const scaledSize = (qr.getModuleCount() + 2 * margin) * cellSize;

                        canvas.width = scaledSize;
                        canvas.height = scaledSize;
                        const ctx = canvas.getContext('2d');

                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, scaledSize, scaledSize);

                        ctx.fillStyle = '#000000';
                        for (let row = 0; row < qr.getModuleCount(); row++) {
                            for (let col = 0; col < qr.getModuleCount(); col++) {
                                if (qr.isDark(row, col)) {
                                    ctx.fillRect(
                                        (col + margin) * cellSize,
                                        (row + margin) * cellSize,
                                        cellSize,
                                        cellSize
                                    );
                                }
                            }
                        }

                        qrCodeElement.appendChild(canvas);

                    } catch (error) {
                        console.error('QR Generation Error:', error);
                        qrCodeElement.innerHTML = '<div class="alert alert-danger">Error generating QR code</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching QR code:', error);
                    document.getElementById('qrCode').innerHTML =
                        '<div class="alert alert-danger">Failed to load QR code</div>';
                });
        }

        function startQRRefresh() {
            // Only start refresh if session is active
            if (isSessionActive && !isSessionExpired) {
                // Generate immediately
                generateQRCode();
                // Refresh every 60 seconds
                refreshInterval = setInterval(generateQRCode, 60000);
            }
        }

        // Wait for DOM to be fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startQRRefresh);
        } else {
            startQRRefresh();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>

    <style>
        #qrCode {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 220px;
        }

        #qrCode canvas {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: white;
            max-width: 100%;
            height: auto;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
    </style>
@endpush
