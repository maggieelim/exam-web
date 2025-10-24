@extends('layouts.user_type.auth')

@section('content')
    <div class="row">

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Edit Course</h5>
                </div>
                <div class="card-body px-4 pt-2 pb-2">
                    <form role="form" method="POST" action="/courses/update/{{ $course->slug }}"
                        enctype="multipart/form-data">
                        @csrf

                        <input hidden type="text" name="semester_id" class="form-control" value="{{ $semesterId }}"
                            required>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label>Kode Blok</label>
                                <input type="text" name="kode_blok" class="form-control" value="{{ $course->kode_blok }}"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label>Nama Blok</label>
                                <input type="text" name="name" class="form-control" value="{{ $course->name }}"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label>Semester</label>
                                <select id="semester" name="semester" class="form-select form-select">
                                    <option value="Ganjil/Genap" {{ $course->semester === '' ? 'selected' : '' }}>
                                        Ganjil/Genap
                                    </option>
                                    <option value="Ganjil" {{ $course->semester === 'Ganjil' ? 'selected' : '' }}>Ganjil
                                    </option>
                                    <option value="Genap" {{ $course->semester === 'Genap' ? 'selected' : '' }}>Genap
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="lecturers" class="form-label">Dosen Pengajar</label>
                                <select id="lecturers" name="lecturers[]" multiple class="form-select">
                                    @foreach ($lecturers as $lecturer)
                                        <option value="{{ $lecturer->lecturer->id }}"
                                            @if (isset($selectedLecturers) && in_array($lecturer->lecturer->id, $selectedLecturers->pluck('lecturer_id')->toArray())) selected @endif>
                                            {{ $lecturer->lecturer->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn bg-gradient-primary w-100">Update</button>
                            </div>
                        </div>

                    </form>
                </div>
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
@endpush
