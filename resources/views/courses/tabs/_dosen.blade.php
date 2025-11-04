<div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
    </button>
    <a class="btn bg-gradient-primary btn-sm"
        href="{{ route('courses.addLecturer', ['course' => $course->slug, 'semester_id' => $semesterId]) }}">
        Pilih Dosen
    </a>
</div>

<!-- Collapse Form -->
<div class="collapse" id="filterCollapse">
    <form method="GET" action="{{ route('courses.edit', [$course->slug]) }}#dosen">
        <div class="mx-3 my-2 py-2">
            <div class="row g-2">
                <!-- Input Blok -->
                <input type="hidden" name="semester_id" value="{{ request('semester_id') }}">
                <!-- Input Dosen -->
                <div class="col-md-6">
                    <label for="dosen" class="form-label mb-1">Name</label>
                    <input type="text" class="form-control form-control-sm" name="name"
                        value="{{ request('name') }}">
                </div>
                <div class="col-md-6">
                    <label for="blok" class="form-label mb-1">Bagian</label>
                    <input type="text" class="form-control form-control-sm" name="bagian"
                        value="{{ request('bagian') }}">
                </div>

                <!-- Buttons -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('courses.edit', [$course->slug, 'semester_id' => request('semester_id')]) }}#dosen"
                        class="btn btn-light btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive p-0">
    <form id="lecturerForm" action="{{ route('courses.updateLecturer', $course->slug) }}" method="POST">
        @csrf
        <table class="compact-table table-bordered">
            <thead class="text-center align-middle">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Bagian</th>
                    <th>Pleno</th>
                    <th>Kuliah</th>
                    <th>Praktikum</th>
                    <th>Tutor</th>
                    <th>Skill Lab</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($lecturerData->lecturers as $index => $lecturer)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $lecturer->lecturer->user->name }}</td>
                        <td>{{ $lecturer->lecturer->bagian }}</td>
                        <td class="text-center">
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][pleno]" value="1"
                                {{ $lecturer->hasActivity('Pleno') ? 'checked' : '' }} class="group-checkbox input-bg">
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][kuliah]" value="1"
                                {{ $lecturer->hasActivity('Kuliah') ? 'checked' : '' }}
                                class="group-checkbox input-bg">
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][praktikum]" value="1"
                                {{ $lecturer->hasActivity('Praktikum') ? 'checked' : '' }}
                                class="group-checkbox input-bg">
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][pemicu]" value="1"
                                {{ $lecturer->hasActivity('Pemicu') ? 'checked' : '' }}
                                class="group-checkbox input-bg">
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][skillslab]" value="1"
                                {{ $lecturer->hasActivity('Skill Lab') ? 'checked' : '' }}
                                class="group-checkbox input-bg">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
        </div>
    </form>
</div>
