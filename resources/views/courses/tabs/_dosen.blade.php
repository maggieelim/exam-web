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
                            <input type="checkbox" name="lecturers[{{ $lecturer->id }}][skill lab]" value="1"
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
    <div id="alert-container" class="mt-3"></div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("lecturerForm");
        const alertContainer = document.getElementById("alert-container");

        form.addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;

            // Show loading state
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitButton.disabled = true;

            fetch(form.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response received:', data);

                    if (data.success) {
                        showNotification(data.message, "success");

                        // Reset perubahan visual
                        document.querySelectorAll('.soft-edit').forEach(td => {
                            td.classList.remove('soft-edit');
                        });

                        // Update data-original
                        document.querySelectorAll('.input-bg').forEach(input => {
                            if (input.type === 'checkbox') {
                                input.dataset.original = input.checked ? 'true' : 'false';
                            }
                        });
                    } else {
                        showNotification(data.message || "Gagal menyimpan data", "danger");
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    showNotification("Terjadi kesalahan saat memperbarui data: " + err.message,
                        "danger");
                })
                .finally(() => {
                    // Restore button
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });
        });



        // Tambahkan event listener untuk debug perubahan checkbox
        document.querySelectorAll('.input-bg[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                console.log('Checkbox changed:', this.name, this.checked);
            });
        });
    });
</script>
