@extends('layouts.user_type.auth')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h4 class="text-uppercase">PENJADWALAN PSSK - Blok {{ $course->name }}</h4>

                    <ul class="nav nav-tabs card-header-tabs" id="courseTabs" role="tablist">
                        {{-- Hapus class 'active' dari semua tab --}}
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#kelas" role="tab">KELAS</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#siswa"
                                role="tab">SISWA</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dosen"
                                role="tab">DOSEN</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#praktikum"
                                role="tab">PRAKTIKUM</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pemicu"
                                role="tab">PEMICU</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pleno"
                                role="tab">PLENO</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#skilllab" role="tab">SKILL
                                LAB</a></li>
                    </ul>
                </div>

                <div class="card-body px-4 pt-3 pb-2 tab-content">
                    {{-- Hapus class 'show active' dari semua pane --}}
                    <div class="tab-pane fade" id="kelas" role="tabpanel">
                        @include('courses.tabs._kelas')
                    </div>
                    <div class="tab-pane fade" id="siswa" role="tabpanel">
                        @include('courses.tabs._siswa')
                    </div>
                    <div class="tab-pane fade" id="dosen" role="tabpanel">
                        @include('courses.tabs._dosen')
                    </div>
                    <div class="tab-pane fade" id="praktikum" role="tabpanel">
                        @include('courses.tabs._praktikum')
                    </div>
                    <div class="tab-pane fade" id="pemicu" role="tabpanel">
                        @include('courses.tabs._pemicu')
                    </div>
                    <div class="tab-pane fade" id="pleno" role="tabpanel">
                        @include('courses.tabs._pleno')
                    </div>
                    <div class="tab-pane fade" id="skilllab" role="tabpanel">
                        @include('courses.tabs._skilllab')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const courseTabs = document.getElementById('courseTabs');
            const tabLinks = courseTabs.querySelectorAll('a[data-bs-toggle="tab"]');

            // Fungsi untuk mengaktifkan tab
            function activateTab(tabElement) {
                // Hapus class active dari semua tab dan pane
                tabLinks.forEach(link => {
                    link.classList.remove('active');
                    const pane = document.querySelector(link.getAttribute('href'));
                    if (pane) {
                        pane.classList.remove('show', 'active');
                    }
                });

                // Tambah class active ke tab dan pane yang dipilih
                tabElement.classList.add('active');
                const targetPane = document.querySelector(tabElement.getAttribute('href'));
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            }

            // Fungsi untuk mengaktifkan tab berdasarkan hash URL
            function activateTabFromHash() {
                const hash = window.location.hash;
                if (hash) {
                    const tabElement = document.querySelector(`a[href="${hash}"]`);
                    if (tabElement) {
                        activateTab(tabElement);
                        return true;
                    }
                }
                return false;
            }

            // Jika tidak ada hash, aktifkan tab pertama
            if (!activateTabFromHash()) {
                // Default ke tab pertama jika tidak ada hash
                activateTab(tabLinks[0]);
            }

            // Event listener untuk setiap tab link
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    const targetId = this.getAttribute('href');

                    // Aktifkan tab
                    activateTab(this);

                    // Update URL hash
                    if (history.pushState) {
                        history.pushState(null, null, targetId);
                    } else {
                        window.location.hash = targetId;
                    }
                });
            });

            // Tangani perubahan hash manual (back/forward browser)
            window.addEventListener('hashchange', activateTabFromHash);
        });
    </script>
@endsection
