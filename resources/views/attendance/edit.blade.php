@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">
    <div class="card">
        <div class="card-header pb-3">
            <h5 class="mb-1">Create New Attendance</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">
            <form method="POST" action="{{ route('attendance.update', $attendance->absensi_code) }}">
                @csrf
                @method('PUT')

                <!-- Course & Lecturer Section -->
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="semester_id" class="form-label">Semester</label>
                        <input readonly class="form-control"
                            value="{{ $attendance->semester->semester_name }} {{ $attendance->semester->academicYear->year_name }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Blok</label>
                        <input readonly class="form-control" value="{{ $attendance->course->name }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="lecturers" class="form-label">Lecturer(s)</label>
                        <ul class="list-group">
                            @foreach ($lecturers as $lec)
                            <li class="list-group-item">
                                {{ $lec->courseLecturer->lecturer->user->name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Activity</label>
                        <input readonly class="form-control" value="{{ $attendance->activity->activity_name }}" />
                    </div>
                </div>

                <!-- Activity & Time Section -->
                <div class="row">
                    @php
                    $startDate = \Carbon\Carbon::parse($attendance->start_time)->format('Y-m-d');
                    $startTime = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
                    $endTime = \Carbon\Carbon::parse($attendance->end_time)->format('H:i');
                    @endphp
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $startDate }}" readonly>
                    </div>

                    <div class="mb-3 col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="startTime" class="form-control" value="{{ $startTime }}" required
                            readonly>
                    </div>

                    <div class="mb-3 col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="endTime" class="form-control" value="{{ $endTime }}" required readonly>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Tolerance Meter</label>
                        <input type="number" name="tolerance" class="form-control" required value="50">
                    </div>
                </div>

                <!-- Location Section -->
                <div class="row">
                    <div class="mb-3 col-md-9">
                        <label for="location_address" class="form-label">Address / Street Name</label>
                        <div class="input-group">
                            <input type="text" id="location_address" name="location_address" class="form-control"
                                value="{{ $attendance->loc_name }}">
                            <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                        </div>
                    </div>

                    <input type="hidden" id="location_lat" name="location_lat" value="{{ $attendance->location_lat }}"
                        readonly required>
                    <input type="hidden" id="location_long" name="location_long"
                        value="{{ $attendance->location_long }}" readonly required>

                    <div class="mb-3 col-md-3">
                        <label for="location_accuracy" class="form-label">Accuracy (meters)</label>
                        <div class="input-group">
                            <input type="text" id="location_accuracy" class="form-control" readonly>
                            <span class="input-group-text"><i class="fas fa-crosshairs"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <button type="button" class="btn btn-primary btn-sm" id="getLocationBtn"
                            onclick="getHighAccuracyLocation()">
                            <i class="fas fa-location-arrow me-1"></i> Get Location
                        </button>
                        <div id="locationStatus" class="small"></div>
                    </div>

                    <!-- Map for Location Verification -->
                    <div class="mb-3 col-md-8" id="mapContainer" style="display: none;">
                        <label class="form-label">Location Map</label>
                        <div id="map" style="height: 300px; width: 100%; border-radius: 8px;"></div>
                        <small class="text-muted">Verify your location on the map</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetForm()">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-sm bg-gradient-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('dashboard')
<script>
    document.addEventListener("DOMContentLoaded", function() {
            // Initialize Choices for multiple select
            const multipleSelect = new Choices('#lecturers', {
                removeItemButton: true,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Select lecturers',
                searchPlaceholderValue: 'Search lecturers...'
            });

            // Add form validation
            document.getElementById('attendanceForm').addEventListener('submit', function(e) {
                const lat = document.getElementById('location_lat').value;
                const lng = document.getElementById('location_long').value;

                if (!lat || !lng) {
                    e.preventDefault();
                    alert('Please get your location before submitting');
                    document.getElementById('getLocationBtn').focus();
                }
            });
        });

        function resetForm() {
            if (confirm('Are you sure you want to reset the form?')) {
                document.getElementById('attendanceForm').reset();
                // Reset location fields
                document.getElementById('location_address').value = '';
                document.getElementById('location_accuracy').value = '';
                document.getElementById('location_lat').value = '';
                document.getElementById('location_long').value = '';
                document.getElementById('locationStatus').innerHTML = '';
                document.getElementById('mapContainer').style.display = 'none';

                // Reset to today's date
                const today = new Date().toISOString().split('T')[0];
                document.querySelector('input[name="date"]').value = today;
            }
        }
</script>

<!-- Include Leaflet CSS & JS for maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const lat = document.getElementById("location_lat").value;
    const lng = document.getElementById("location_long").value;

    // Jika attendance sudah punya lokasi → tampilkan otomatis
    if (lat && lng) {
        const latNum = parseFloat(lat);
        const lngNum = parseFloat(lng);

        getAddressFromCoordinates(latNum, lngNum); 
        showMap(latNum, lngNum); // tampilkan map + marker
        document.getElementById('mapContainer').style.display = 'block';
    }
});
    let watchId = null;
        let map = null;
        let marker = null;
        let accuracyCircle = null;

        function getHighAccuracyLocation() {
            const statusElement = document.getElementById('locationStatus');
            const getLocationBtn = document.getElementById('getLocationBtn');

            getLocationBtn.disabled = true;
            getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Getting Location...';
            statusElement.innerHTML = '<span class="text-warning">Getting high accuracy location...</span>';

            if (!navigator.geolocation) {
                statusElement.innerHTML = '<span class="text-danger">Browser does not support geolocation</span>';
                resetLocationButton();
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            };

            watchId = navigator.geolocation.watchPosition(showHighAccuracyPosition, showError, options);

            setTimeout(() => {
                if (watchId) {
                    navigator.geolocation.clearWatch(watchId);
                    if (statusElement.innerHTML.includes('Getting high accuracy location')) {
                        statusElement.innerHTML = '<span class="text-info">Location scanning completed</span>';
                    }
                    resetLocationButton();
                }
            }, 20000);
        }

        function resetLocationButton() {
            const getLocationBtn = document.getElementById('getLocationBtn');
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> Get Location';
        }

        function showHighAccuracyPosition(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            document.getElementById("location_lat").value = lat.toFixed(8);
            document.getElementById("location_long").value = lng.toFixed(8);
            document.getElementById("location_accuracy").value = accuracy.toFixed(2) + " meters";

            getAddressFromCoordinates(lat, lng);
            showMap(lat, lng, accuracy);
            resetLocationButton();
        }

        // === Reverse geocoding ===
        function getAddressFromCoordinates(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById("location_address").value = data.display_name;
                    } else {
                        document.getElementById("location_address").value = "Address not found";
                    }
                })
                .catch(error => {
                    console.error("Error fetching address:", error);
                    document.getElementById("location_address").value = "Failed to load address";
                });
        }

        // === Forward geocoding ===
        function getCoordinatesFromAddress(address) {
            if (!address) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        document.getElementById("location_lat").value = lat.toFixed(8);
                        document.getElementById("location_long").value = lng.toFixed(8);
                        showMap(lat, lng);
                    } else {
                        alert('Location not found. Please refine your address.');
                    }
                })
                .catch(error => console.error("Error fetching coordinates:", error));
        }

        function showMap(lat, lng, accuracy = 0) {
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.style.display = 'block';

            if (!map) {
                map = L.map('map').setView([lat, lng], 18);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // event ketika user pindahkan pin
                map.on('click', function(e) {
                    const newLat = e.latlng.lat;
                    const newLng = e.latlng.lng;
                    document.getElementById("location_lat").value = newLat.toFixed(8);
                    document.getElementById("location_long").value = newLng.toFixed(8);
                    getAddressFromCoordinates(newLat, newLng);
                    showMap(newLat, newLng);
                });
            } else {
                map.setView([lat, lng], 18);
            }

            if (marker) {
                map.removeLayer(marker);
            }
            if (accuracyCircle) {
                map.removeLayer(accuracyCircle);
            }

            marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map)
                .bindPopup(`Drag to adjust location`).openPopup();

            // event ketika marker digeser manual
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                document.getElementById("location_lat").value = pos.lat.toFixed(8);
                document.getElementById("location_long").value = pos.lng.toFixed(8);
                getAddressFromCoordinates(pos.lat, pos.lng);
            });

            if (accuracy > 0) {
                accuracyCircle = L.circle([lat, lng], {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.1,
                    radius: accuracy
                }).addTo(map);
            }
        }

        function showError(error) {
            const statusElement = document.getElementById('locationStatus');

            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }

            switch (error.code) {
                case error.PERMISSION_DENIED:
                    statusElement.innerHTML = '<span class="text-danger">Location permission denied</span>';
                    break;
                case error.POSITION_UNAVAILABLE:
                    statusElement.innerHTML = '<span class="text-danger">Location information unavailable</span>';
                    break;
                case error.TIMEOUT:
                    statusElement.innerHTML = '<span class="text-danger">Location request timeout</span>';
                    break;
                default:
                    statusElement.innerHTML = '<span class="text-danger">An unknown error occurred</span>';
            }

            resetLocationButton();
        }

        // === Event untuk forward geocoding ===
        document.getElementById('location_address').addEventListener('change', function() {
            getCoordinatesFromAddress(this.value);
        });

        window.addEventListener('beforeunload', function() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
        });
</script>

<style>
    #map {
        border: 1px solid #dee2e6;
    }
</style>
@endpush