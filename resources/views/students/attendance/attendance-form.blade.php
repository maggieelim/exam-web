@extends('layouts.user_type.auth')

@section('content')
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header ">
                <h4 class="mb-0">
                    <i class="fas fa-user-check me-2"></i>
                    Attendance Submission
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <h6 class="text-muted">Session Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Course</strong></td>
                            <td><strong>:</strong></td>
                            <td>{{ $attendanceSession->course->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Activity</strong></td>
                            <td><strong>:</strong></td>
                            <td>{{ $attendanceSession->activity->activity_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Time</strong></td>
                            <td><strong>:</strong></td>
                            <td>
                                {{ \Carbon\Carbon::parse($attendanceSession->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($attendanceSession->end_time)->format('H:i') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <form id="attendanceForm" method="POST"
                    action="{{ route('student.attendance.submit', $attendanceSession->id) }}">
                    @csrf

                    <!-- Hidden fields untuk data dari QR code dan lokasi -->
                    <input type="hidden" name="token" id="tokenInput">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="wifi_ssid" id="wifiSsid">
                    <input type="hidden" name="loc_name" id="loc_name">
                    <input type="hidden" name="distance" id="distance">

                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM *</label>
                        <input type="text" class="form-control" id="nim" name="nim"
                            value="{{ $student->student->nim }}" required placeholder="Enter your student ID">
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ $student->name }}" required placeholder="Enter your full name">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="locationPermission" required>
                            <label class="form-check-label" for="locationPermission">
                                I allow this site to access my location for attendance verification
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i>
                        Submit Attendance
                    </button>
                </form>

                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Your location and device information will be recorded for verification
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('attendanceForm');
        const submitBtn = document.getElementById('submitBtn');
        const locationPermission = document.getElementById('locationPermission');

        // Extract token from URL parameters (jika ada)
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            document.getElementById('tokenInput').value = token;
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!locationPermission.checked) {
                alert('Please allow location access to submit attendance.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

            try {
                // Get current location
                const position = await getCurrentLocation();
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Hitung jarak dengan lokasi sesi
                const sessionLat = {{ $attendanceSession->location_lat }};
                const sessionLng = {{ $attendanceSession->location_long }};
                const distance = calculateDistance(lat, lng, sessionLat, sessionLng);
                document.getElementById('distance').value = distance.toFixed(2);

                // Dapatkan nama lokasi (reverse geocode)
                const locName = await getLocationName(lat, lng);
                document.getElementById('loc_name').value = locName;

                // WiFi info (optional)
                document.getElementById('wifiSsid').value = 'unknown';

                form.submit();

            } catch (error) {
                console.error('Error:', error);
                alert('Failed to get your location. Please ensure location services are enabled.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Submit Attendance';
            }
        });

        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });
        }

        // Hitung jarak (meter) antara dua koordinat
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // radius bumi (meter)
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c;
        }

        // Ambil nama lokasi dari koordinat (gunakan Nominatim OpenStreetMap)
        async function getLocationName(lat, lng) {
            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                const data = await res.json();
                return data.display_name || 'Unknown Location';
            } catch (e) {
                console.warn('Failed to get location name:', e);
                return 'Unknown Location';
            }
        }
    });
</script>
