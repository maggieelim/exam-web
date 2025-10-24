@extends('layouts.user_type.auth')

@section('content')
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Create New Attendance</h5>
            </div>
            <div class="card-body px-4 pt-2 pb-2">
                <form method="POST" action="{{ route('attendance.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label>Blok</label>
                            <select id="course" name="course" class="form-select">
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="lecturers" class="form-label">Lecturer</label>
                            <select id="lecturers" name="lecturers[]" multiple class="form-select">
                                @foreach ($lecturers as $lec)
                                    <option value="{{ $lec->id }}" @if ($lecturer && $lecturer->id == $lec->id) selected @endif>
                                        {{ $lec->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label>Activity</label>
                            <select id="activity" name="activity" class="form-select">
                                @foreach ($activity as $act)
                                    <option value="{{ $act->id }}">{{ $act->activity_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label>Start Time</label>
                            <input type="time" name="startTime" class="form-control" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label>End Time</label>
                            <input type="time" name="endTime" class="form-control" required>
                        </div>

                        <!-- Lokasi Section yang Diperbaiki -->
                        <div class="mb-3 col-md-6">
                            <label for="location_address" class="form-label">Alamat / Nama Jalan</label>
                            <input type="text" id="location_address" name="location_address" class="form-control"
                                readonly>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="location_lat" class="form-label">Latitude</label>
                            <input type="text" id="location_lat" name="location_lat" class="form-control" readonly
                                required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="location_long" class="form-label">Longitude</label>
                            <input type="text" id="location_long" name="location_long" class="form-control" readonly
                                required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="location_accuracy" class="form-label">Akurasi (meter)</label>
                            <input type="text" id="location_accuracy" class="form-control" readonly>
                        </div>
                        <div class="mb-3 col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="getHighAccuracyLocation()">
                                Ambil Lokasi
                            </button>
                            <div id="locationStatus" class="text-sm"></div>
                        </div>

                        <!-- Tampilkan Map untuk Verifikasi -->
                        <div class="mb-3 col-md-12" id="mapContainer" style="display: none;">
                            <label>Peta Lokasi</label>
                            <div id="map" style="height: 300px; width: 100%; border-radius: 8px;"></div>
                            <small class="text-muted">Verifikasi lokasi Anda di peta</small>
                        </div>

                        <div class="mb-3 col-md-12">
                            <label>Semester</label>
                            <select id="semester" name="semester" class="form-select form-select">
                                <option value="Ganjil/Genap">Ganjil/Genap</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn bg-gradient-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('dashboard')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const multipleSelect = new Choices('#lecturers', {
                removeItemButton: true,
                searchEnabled: true
            });
        });
    </script>

    <!-- Include Leaflet CSS & JS untuk peta -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let watchId = null;
        let map = null;
        let marker = null;

        function getHighAccuracyLocation() {
            const statusElement = document.getElementById('locationStatus');
            statusElement.innerHTML = '<span class="text-warning">Mengambil lokasi dengan akurasi tinggi...</span>';

            if (!navigator.geolocation) {
                statusElement.innerHTML = '<span class="text-danger">Browser tidak mendukung geolocation</span>';
                return;
            }

            // Options untuk high accuracy
            const options = {
                enableHighAccuracy: true, // High accuracy mode
                timeout: 10000, // Timeout 10 detik
                maximumAge: 0 // Tidak menggunakan cached position
            };

            // Gunakan watchPosition untuk mendapatkan pembaruan terus menerus
            watchId = navigator.geolocation.watchPosition(
                showHighAccuracyPosition,
                showError,
                options
            );

            // Stop setelah 15 detik
            setTimeout(() => {
                if (watchId) {
                    navigator.geolocation.clearWatch(watchId);
                    statusElement.innerHTML = '<span class="text-info">Pemindaian lokasi selesai</span>';
                }
            }, 15000);
        }

        function showHighAccuracyPosition(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            // Update form fields
            document.getElementById("location_lat").value = lat.toFixed(8);
            document.getElementById("location_long").value = lng.toFixed(8);
            document.getElementById("location_accuracy").value = accuracy.toFixed(2) + " meter";

            getAddressFromCoordinates(lat, lng);
            const statusElement = document.getElementById('locationStatus');

            // Tampilkan status berdasarkan akurasi
            if (accuracy <= 10) {
                statusElement.innerHTML = '<span class="text-success">✔ Akurasi sangat tinggi (' + accuracy.toFixed(2) +
                    'm)</span>';
            } else if (accuracy <= 30) {
                statusElement.innerHTML = '<span class="text-success">✔ Akurasi baik (' + accuracy.toFixed(2) + 'm)</span>';
            } else if (accuracy <= 100) {
                statusElement.innerHTML = '<span class="text-warning">✓ Akurasi cukup (' + accuracy.toFixed(2) +
                    'm)</span>';
            } else {
                statusElement.innerHTML = '<span class="text-danger">✗ Akurasi rendah (' + accuracy.toFixed(2) +
                    'm) - Coba lagi</span>';
            }

            // Tampilkan peta untuk verifikasi
            showMap(lat, lng, accuracy);
        }

        function getAddressFromCoordinates(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        const address = data.display_name || "Alamat tidak ditemukan";
                        document.getElementById("location_address").value = address;
                    } else {
                        document.getElementById("location_address").value = "Alamat tidak ditemukan";
                    }
                })
                .catch(error => {
                    console.error("Error mengambil alamat:", error);
                    document.getElementById("location_address").value = "Gagal memuat alamat";
                });
        }

        function showMap(lat, lng, accuracy) {
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.style.display = 'block';

            if (!map) {
                // Initialize map
                map = L.map('map').setView([lat, lng], 18);

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
            } else {
                map.setView([lat, lng], 18);
            }

            // Remove existing marker
            if (marker) {
                map.removeLayer(marker);
            }

            // Add new marker dengan accuracy circle
            marker = L.marker([lat, lng]).addTo(map)
                .bindPopup(`Lokasi Anda<br>Akurasi: ${accuracy.toFixed(2)} meter`)
                .openPopup();

            // Add accuracy circle
            L.circle([lat, lng], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.1,
                radius: accuracy
            }).addTo(map);
        }

        function showError(error) {
            const statusElement = document.getElementById('locationStatus');

            // Stop watching jika ada error
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }

            switch (error.code) {
                case error.PERMISSION_DENIED:
                    statusElement.innerHTML =
                        '<span class="text-danger">Izin lokasi ditolak. Izinkan akses lokasi di browser settings.</span>';
                    break;
                case error.POSITION_UNAVAILABLE:
                    statusElement.innerHTML =
                        '<span class="text-danger">Informasi lokasi tidak tersedia. Pastikan GPS/Location Services aktif.</span>';
                    break;
                case error.TIMEOUT:
                    statusElement.innerHTML =
                        '<span class="text-danger">Timeout. Coba lagi di area dengan sinyal GPS yang baik.</span>';
                    break;
                default:
                    statusElement.innerHTML = '<span class="text-danger">Terjadi kesalahan tak dikenal.</span>';
            }
        }

        // Cleanup ketika page unload
        window.addEventListener('beforeunload', function() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
        });
    </script>

    <style>
        #mapContainer {
            margin-top: 20px;
        }

        .text-success {
            color: #28a745;
        }

        .text-warning {
            color: #ffc107;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-info {
            color: #17a2b8;
        }
    </style>
@endpush
