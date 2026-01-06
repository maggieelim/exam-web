@extends('layouts.user_type.auth')

@section('content')
<div>
    {{-- Header Halaman + Tombol Upload Excel --}}
    <div class="card-header d-flex flex-row justify-content-between mb-0 pb-0 p-3">
        <div>
            <h5 class="mb-0">Manage Questions - {{ $exam->title }}</h5>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if ($exam->status === 'upcoming')
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reuploadModal">
                {{ $questions->count() === 0 ? 'Upload Questions' : 'Reupload Questions' }}
            </button>
            @endif

        </div>
    </div>

    <!-- Modal Upload Excel -->
    <div class="modal fade" id="reuploadModal" tabindex="-1" aria-labelledby="reuploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reuploadModalLabel">
                        {{ $questions->count() === 0 ? 'Upload' : 'Reupload' }}
                        Questions via Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('exams.questions.updateByExcel', $exam->exam_code) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xls, .xlsx)</label>
                            <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="collapse" id="filterCollapse">
        <form method="GET" action="{{ route('exams.questions.' . $status, $exam->exam_code) }}">
            <div class="mx-3 my-2 py-2">
                <div class="row g-2">
                    <div class="col-md-8">
                        <label for="title" class="form-label mb-1">Soal</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari Soal Ujian"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label mb-1">Category</label>
                        <select name="category" class="form-control">
                            <option value="">-- All Category --</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category')==$category->id ? 'selected' : ''
                                }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('exams.questions.' . $status, $exam->exam_code) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @php
    $currentPage = $questions->currentPage();
    $perPage = $questions->perPage();
    $startNumber = ($currentPage - 1) * $perPage + 1;
    @endphp

    @foreach ($questions as $index => $question)
    <div class="card mb-4 question-card" id="question-{{ $question->id }}">
        <div class="card-header pb-0 px-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Soal {{ $startNumber + $index }}</h6>
            <div class="d-flex gap-3">
                <div class="d-flex flex-column text-end">
                    <small class="text-muted">Exam: {{ $exam->title }}</small>
                    <small class="text-muted">
                        Category: {{ $question->category ? $question->category->name : 'Tidak ada kategori' }}
                    </small>
                </div>
                <button type="button" class="btn btn-lg btn-link p-0 delete-question-btn" title="Hapus Soal"
                    data-question-id="{{ $question->id }}">
                    <i class="fas text-danger fa-times"></i>
                </button>
            </div>
        </div>

        <div class="card-body pt-0 p-3">
            <form class="question-form" data-question-id="{{ $question->id }}">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label class="form-label">Badan Soal</label>
                    <textarea name="badan_soal" rows=1
                        class="form-control auto-resize">{{ $question->badan_soal }}</textarea>
                </div>

                <div class=" mb-2">
                    <label class="form-label">Kalimat Tanya</label>
                    <textarea name="kalimat_tanya" class="form-control auto-resize"
                        rows="1">{{ $question->kalimat_tanya }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Upload Gambar (Opsional)</label>
                    <input type="file" name="image" id="image-{{ $question->id }}" class="form-control"
                        accept="image/*">

                    <!-- Container untuk gambar -->
                    <div id="image-container-{{ $question->id }}" class="mt-3">
                        @if (!empty($question->image))
                        <div class="position-relative d-inline-block existing-image">
                            <img src="{{ asset('storage/' . $question->image) }}" alt="Soal Image" width="300"
                                class="border rounded">
                            <button type="button"
                                class="btn btn-danger btn-sm rounded-circle align-items-center justify-content-center position-absolute delete-image-btn"
                                style="width: 30px; height: 30px; padding: 0; top: -10px; right: -10px;"
                                data-question-id="{{ $question->id }}">
                                x
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                <input type="hidden" name="delete_image" id="delete_image-{{ $question->id }}" value="0">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Opsi Jawaban</label>

                    <div class="row">
                        @foreach ($question->options as $option)
                        <div class="col-md-6 mb-3" data-option-container="{{ $option->id }}">
                            {{-- HEADER --}}
                            <div class="d-flex align-items-start mb-1">
                                <div class="form-check me-2">
                                    <input type="checkbox" name="options[{{ $option->id }}][is_correct]"
                                        class="form-check-input" {{ $option->is_correct ? 'checked' : '' }}>
                                </div>

                                <span class="fw-bold me-2">{{ $option->option }}.</span>
                                <textarea name="options[{{ $option->id }}][text]" rows=1
                                    class="form-control auto-resize mb-2">{{ $option->text }}</textarea>

                            </div>

                            {{-- IMAGE UPLOAD --}}
                            <div class="d-flex align-items-start justify-content-end gap-2">
                                <input type="file" name="options[{{ $option->id }}][image]" class="form-control w-50"
                                    accept="image/*" data-preview="preview-{{ $option->id }}">
                                <input type="hidden" name="options[{{ $option->id }}][delete_image]"
                                    id="delete-image-{{ $option->id }}" value="0">
                                {{-- IMAGE PREVIEW --}}
                                @if ($option->image)
                                <div class="align-items-end position-relative d-inline-block option-image-container"
                                    id="option-container-{{ $option->id }}"> <img id="preview-{{ $option->id }}"
                                        src="{{ asset('storage/' . $option->image) }}" class="img-thumbnail"
                                        style="max-height:140px; cursor: zoom-in;">

                                    {{-- DELETE BUTTON --}}
                                    <button type="button"
                                        class="btn btn-danger btn-sm rounded-circle delete-option-image"
                                        style="position:absolute; top:-8px; right:-8px; width:26px; height:26px; padding:0;"
                                        data-option-id="{{ $option->id }}">
                                        ✕
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary update-btn">Update Soal</button>
                    <button type="button" class="btn btn-sm btn-warning anulir-btn"
                        data-question-id="{{ $question->id }}">Anulir
                        Soal</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$questions" />
    </div>
</div>

<script>
    function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.auto-resize').forEach(textarea => {
                autoResize(textarea);

                textarea.addEventListener('input', function() {
                    autoResize(this);
                });
            });

            function updateOptionImageDisplay(optionId, imageUrl, hasImage) {
                // Cari container option
                const optionContainer = document.getElementById(`option-container-${optionId}`);
                const fileInput = document.querySelector(`input[name="options[${optionId}][image]"]`);
                const deleteInput = document.getElementById(`delete-image-${optionId}`);

                // Reset file input
                if (fileInput) fileInput.value = '';

                // Reset delete input jika ada gambar baru
                if (hasImage) {
                    deleteInput.value = '0';
                }

                // Hapus preview yang ada
                if (optionContainer) {
                    optionContainer.remove();
                }

                // Jika ada gambar baru, tampilkan
                if (hasImage && imageUrl) {
                    const containerDiv = document.querySelector(`div[data-option-container="${optionId}"]`)
                        ?.closest('.col-md-6') ||
                        document.querySelector(`textarea[name="options[${optionId}][text]"]`)?.closest('.col-md-6');

                    if (containerDiv) {
                        const imageHtml = `
                            <div class="align-items-end position-relative d-inline-block option-image-container"
                                    id="option-container-${optionId}"> <img id="preview-${optionId}"
                                        src="${imageUrl}" class="img-thumbnail"
                                        style="max-height:140px; cursor: zoom-in;">

                                    {{-- DELETE BUTTON --}}
                                    <button type="button"
                                        class="btn btn-danger btn-sm rounded-circle delete-option-image"
                                        style="position:absolute; top:-8px; right:-8px; width:26px; height:26px; padding:0;"
                                        data-option-id="${optionId}">
                                        ✕
                                    </button>
                                </div>
            `;

                        // Tambahkan gambar setelah input file
                        const fileInputWrapper = containerDiv.querySelector('.d-flex.align-items-center.gap-2');
                        if (fileInputWrapper) {
                            fileInputWrapper.insertAdjacentHTML('afterend', imageHtml);
                        } else {
                            containerDiv.insertAdjacentHTML('beforeend', imageHtml);
                        }

                        // Re-attach event listener untuk tombol delete yang baru
                        const newDeleteBtn = document.querySelector(
                            `#option-container-${optionId} .delete-option-image`);
                        if (newDeleteBtn) {
                            newDeleteBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const optionId = this.dataset.optionId;
                                const form = this.closest('.question-form');

                                document.getElementById(`delete-image-${optionId}`).value = '1';
                                this.closest('.option-image-container').remove();

                                // Auto submit form untuk menghapus gambar
                                form.dispatchEvent(new Event('submit'));
                            });
                        }
                    }
                }
            }
            // Fungsi untuk update tampilan gambar
            function updateImageDisplay(questionId, imageUrl, hasImage) {
                const imageContainer = document.getElementById(`image-container-${questionId}`);
                const deleteInput = document.getElementById(`delete_image-${questionId}`);
                const fileInput = document.getElementById(`image-${questionId}`);

                // Reset file input
                fileInput.value = '';

                // Reset delete input jika ada gambar baru
                if (hasImage) {
                    deleteInput.value = '0';
                }

                // Hapus gambar yang ada
                const existingImage = imageContainer.querySelector('.existing-image');
                if (existingImage) {
                    existingImage.remove();
                }

                // Jika ada gambar baru, tampilkan
                if (hasImage && imageUrl) {
                    const imageHtml = `
                <div class="position-relative d-inline-block existing-image">
                    <img src="${imageUrl}" alt="Soal Image" width="300" class="border rounded">
                    <button type="button" class="btn btn-danger btn-sm rounded-circle align-items-center justify-content-center position-absolute delete-image-btn" 
                        style="width: 30px; height: 30px; padding: 0; top: -10px; right: -10px;" 
                        data-question-id="${questionId}">
                        x
                    </button>
                </div>
            `;
                    imageContainer.insertAdjacentHTML('beforeend', imageHtml);

                    // Re-attach event listener untuk tombol delete yang baru
                    const newDeleteBtn = imageContainer.querySelector('.delete-image-btn');
                    newDeleteBtn.addEventListener('click', function() {
                        const questionId = this.dataset.questionId;
                        const deleteInput = document.getElementById(`delete_image-${questionId}`);
                        const form = document.querySelector(
                            `.question-form[data-question-id="${questionId}"]`);

                        deleteInput.value = '1';
                        this.closest('.existing-image').remove();

                        // Auto submit form untuk menghapus gambar
                        form.dispatchEvent(new Event('submit'));
                    });
                }
            }

            // Handle update soal
            document.querySelectorAll('.question-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const questionId = this.dataset.questionId;
                    const updateBtn = this.querySelector('.update-btn');

                    // Disable button selama proses
                    updateBtn.disabled = true;
                    updateBtn.textContent = 'Updating...';

                    fetch(`/exams/{{ $exam->exam_code }}/questions/${questionId}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                // Update tampilan gambar jika ada perubahan
                                if (data.data && data.data.image_url !== undefined) {
                                    updateImageDisplay(questionId, data.data.image_url, data
                                        .data.has_image);
                                }

                                // Update tampilan gambar option jika ada perubahan
                                if (data.data && data.data.options) {
                                    Object.keys(data.data.options).forEach(optionId => {
                                        const optionData = data.data.options[optionId];
                                        if (optionData.image_url !== undefined) {
                                            updateOptionImageDisplay(optionId,
                                                optionData.image_url, optionData
                                                .has_image);
                                        }
                                    });
                                }
                            } else {
                                showNotification('Terjadi kesalahan saat update soal', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Terjadi kesalahan saat update soal', 'error');
                        })
                        .finally(() => {
                            updateBtn.disabled = false;
                            updateBtn.textContent = 'Update Soal';
                        });
                });
            });

            // Handle anulir soal
            document.querySelectorAll('.anulir-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;

                    if (!confirm(
                            'Yakin ingin menganulir soal ini? Semua jawaban akan dianggap benar.'
                        )) {
                        return;
                    }

                    const form = document.querySelector(
                        `.question-form[data-question-id="${questionId}"]`);
                    const formData = new FormData(form);
                    formData.append('action', 'anulir');

                    // Disable button selama proses
                    this.disabled = true;
                    this.textContent = 'Menganulir...';

                    fetch(`/exams/{{ $exam->exam_code }}/questions/${questionId}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                // Reset semua checkbox menjadi checked
                                const checkboxes = form.querySelectorAll(
                                    'input[type="checkbox"]');
                                checkboxes.forEach(checkbox => {
                                    checkbox.checked = true;
                                });
                            } else {
                                showNotification('Terjadi kesalahan saat menganulir soal',
                                    'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Terjadi kesalahan saat menganulir soal', 'error');
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.textContent = 'Anulir Soal';
                        });
                });
            });

            // Handle delete image soal utama (tanpa konfirmasi)
            document.querySelectorAll('.delete-image-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;
                    const deleteInput = document.getElementById(`delete_image-${questionId}`);
                    const form = document.querySelector(
                        `.question-form[data-question-id="${questionId}"]`);

                    deleteInput.value = '1';
                    this.closest('.existing-image').remove();

                    // Auto submit form untuk menghapus gambar
                    form.dispatchEvent(new Event('submit'));
                });
            });

            // Handle delete image opsi dengan auto submit (tanpa konfirmasi)
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-option-image')) {
                    const optionId = e.target.dataset.optionId;
                    const form = e.target.closest('.question-form');
                    if (!form) return;

                    // set delete flag
                    document.getElementById('delete-image-' + optionId).value = 1;

                    // hide preview
                    const container = document.getElementById('option-container-' + optionId);
                    if (container) {
                        container.remove();
                    }

                    // reset file input
                    const fileInput = form.querySelector(
                        `input[name="options[${optionId}][image]"]`
                    );
                    if (fileInput) fileInput.value = '';

                    // Auto submit form untuk menghapus gambar option
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // Handle delete soal
            document.querySelectorAll('.delete-question-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;

                    if (!confirm('Yakin ingin menghapus soal ini?')) {
                        return;
                    }

                    fetch(`{{ url('exams/' . $exam->exam_code) }}/questions/${questionId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                // Hapus card soal dari DOM
                                document.getElementById(`question-${questionId}`).remove();
                            } else {
                                showNotification('Terjadi kesalahan saat menghapus soal',
                                    'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Terjadi kesalahan saat menghapus soal', 'error');
                        });
                });
            });
        });
</script>
@endsection