@extends('layouts.user_type.auth')
@section('content')
@include('exams.newQuestion')

<div>
    {{-- Header Halaman + Tombol Upload Excel --}}
    <div class="card d-flex flex-row justify-content-between mb-2 pb-0 p-3">
        <div>
            <h5 class="mb-0">Manage Questions - {{ $exam->title }}</h5>
        </div>
        <div class="d-flex gap-2">
            @if ($exam->status === 'upcoming')
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#newQuestionModal">
                + Question
            </button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reuploadModal">
                {{ $questions->count() === 0 ? 'Upload Questions' : 'Reupload Questions' }}
            </button>
            @endif
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
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

    @forelse ($questions as $index => $question)
    <div class="card px-3 mb-4 question-card" id="question-{{ $question->id }}">
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

                <div class="mb-2">
                    <label class="form-label">Kalimat Tanya</label>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <textarea name="kalimat_tanya" class="form-control auto-resize"
                            rows="1">{{ $question->kalimat_tanya }}</textarea>
                        @unless ($question->image)
                        <label class="d-flex align-items-center p-0 mb-0 " style="cursor: pointer;">
                            <div class="position-relative">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fa-regular fa-image" style="color: #5f6368; font-size: 23px;"></i>
                                </div>
                                <input type="file" name="image" id="image-{{ $question->id }}"
                                    class="d-none option-image-input" accept="image/*">
                            </div>
                        </label>
                        @endunless
                    </div>
                </div>

                <!-- Container untuk gambar -->
                <div id="image-container-{{ $question->id }}" class="my-3">
                    @if (!empty($question->image))
                    <div class="position-relative d-inline-block existing-image">
                        <img src="{{ asset('storage/' . $question->image) }}" alt="Soal Image" width="150"
                            class="border rounded">
                        <button type="button"
                            class="btn btn-danger btn-sm rounded-circle align-items-center justify-content-center position-absolute delete-image-btn"
                            style="position:absolute; top:-8px; right:-8px; width:24px; height:24px; padding:0;"
                            data-question-id="{{ $question->id }}">
                            <i class="fas fa-times" style="font-size: 15px"></i> </button>
                    </div>
                    @endif
                </div>
                <input type="hidden" name="delete_image" id="delete_image-{{ $question->id }}" value="0">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Opsi Jawaban</label>

                    <div class="row">
                        @foreach ($question->options as $option)
                        <div class="col-md-12 mb-3" data-option-container="{{ $option->id }}">
                            {{-- HEADER --}}
                            <div class="d-flex align-items-center mb-1">
                                <div class="form-check">
                                    <input type="checkbox" name="options[{{ $option->id }}][is_correct]"
                                        class="form-check-input" {{ $option->is_correct ? 'checked' : '' }}>
                                </div>
                                <span class="fw-bold me-2">{{ $option->option }}.</span>
                                <textarea name="options[{{ $option->id }}][text]" rows=1
                                    class="form-control auto-resize me-2">{{ $option->text }}</textarea>
                                @unless ($option->image)
                                <label class="d-flex align-items-center p-0 mb-0 " style="cursor: pointer;">
                                    <div class="position-relative">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fa-regular fa-image" style="color: #5f6368; font-size: 23px;"></i>
                                        </div>
                                        <input type="file" name="options[{{ $option->id }}][image]"
                                            class="d-none option-image-input" accept="image/*"
                                            data-preview="preview-{{ $option->id }}" data-option-id="{{ $option->id }}">
                                    </div>
                                </label>
                                @endunless
                            </div>

                            <input type="hidden" name="options[{{ $option->id }}][delete_image]"
                                id="delete-image-{{ $option->id }}" value="0">
                            {{-- IMAGE PREVIEW --}}
                            @if ($option->image)
                            <div class="mx-5 position-relative d-inline-block option-image-container mt-2"
                                id="option-container-{{ $option->id }}"> <img id="preview-{{ $option->id }}"
                                    src="{{ asset('storage/' . $option->image) }}" class="img-thumbnail"
                                    style="max-height:120px; cursor: zoom-in;">

                                {{-- DELETE BUTTON --}}
                                <button type="button" class="btn btn-danger btn-sm rounded-circle delete-option-image"
                                    style="position:absolute; top:-8px; right:-8px; width:24px; height:24px; padding:0;"
                                    data-option-id="{{ $option->id }}">
                                    <i class="fas fa-times" style="font-size: 15px"></i> </button>
                                </button>
                            </div>
                            @endif
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
    @empty
    <div class="card">
        <div class="card-body text-center">
            <p class="mb-0">Tidak ada soal ditemukan.</p>
        </div>
    </div>
    @endforelse

    <div class="d-flex justify-content-center mt-3">
        <x-pagination :paginator="$questions" />
    </div>
</div>

<script>
    function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 2 + 'px';
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk handle upload gambar opsi (tanpa tombol update)
            document.querySelectorAll('.option-image-input').forEach(input => {
                input.addEventListener('change', function(e) {
                    const optionId = this.dataset.optionId;
                    const previewId = this.dataset.preview;
                    const form = this.closest('.question-form');
                    const deleteInput = document.getElementById(`delete-image-${optionId}`);

                    // Reset delete flag jika ada gambar baru
                    if (deleteInput) {
                        deleteInput.value = '0';
                    }

                    const file = this.files[0];
                    if (!file) return;

                    // Tampilkan preview sementara
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Hapus gambar lama jika ada
                        const existingContainer = document.getElementById(
                            `option-container-${optionId}`);
                        if (existingContainer) {
                            existingContainer.remove();
                        }

                        // Cari container untuk menempatkan preview
                        const optionDiv = document.querySelector(
                                `div[data-option-container="${optionId}"]`)?.closest(
                                '.col-md-12') ||
                            document.querySelector(
                                `textarea[name="options[${optionId}][text]"]`)?.closest(
                                '.col-md-12');

                        if (optionDiv) {
                            const imageHtml = `
                    <div class="mx-5 position-relative d-inline-block option-image-container mt-2" id="option-container-${optionId}">
                        <img id="preview-${optionId}" src="${e.target.result}" class="img-thumbnail" style="max-height:120px; cursor: zoom-in;">
                    
                        <!-- DELETE BUTTON -->
                        <button type="button" class="btn btn-danger btn-sm rounded-circle delete-option-image"
                            style="position:absolute; top:-9px; right:-8px; width:24px; height:24px; padding:0;"
                            data-option-id="${optionId}">
                                                            <i class="fas fa-times" style="font-size: 15px"></i> </button>
                        </button>
                    </div>
                `;

                            // Tambahkan preview setelah input file
                            const fileInputWrapper = optionDiv.querySelector(
                                '.d-flex.align-items-center.gap-2');
                            if (fileInputWrapper) {
                                fileInputWrapper.insertAdjacentHTML('afterend', imageHtml);
                            } else {
                                optionDiv.insertAdjacentHTML('beforeend', imageHtml);
                            }

                            // Re-attach event listener untuk tombol delete
                            const newDeleteBtn = document.querySelector(
                                `#option-container-${optionId} .delete-option-image`);
                            if (newDeleteBtn) {
                                newDeleteBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const optionId = this.dataset.optionId;
                                    const deleteInput = document.getElementById(
                                        `delete-image-${optionId}`);
                                    const fileInput = form.querySelector(
                                        `input[name="options[${optionId}][image]"]`);

                                    if (deleteInput) deleteInput.value = '1';
                                    if (fileInput) fileInput.value = '';
                                    this.closest('.option-image-container').remove();

                                    // Auto submit form untuk menghapus gambar
                                    form.dispatchEvent(new Event('submit'));
                                });
                            }

                            // Auto submit form untuk upload gambar baru
                            setTimeout(() => {
                                form.dispatchEvent(new Event('submit'));
                            }, 300);
                        }
                    };
                    reader.readAsDataURL(file);
                });
            });
            document.querySelectorAll('.auto-resize').forEach(textarea => {
                autoResize(textarea);

                textarea.addEventListener('input', function() {
                    autoResize(this);
                });
            });

            document.querySelectorAll('input[name="image"]').forEach(input => {
                input.addEventListener('change', function(e) {
                    const questionId = this.id.split('-')[1];
                    const form = document.querySelector(
                        `.question-form[data-question-id="${questionId}"]`);
                    const imageContainer = document.getElementById(`image-container-${questionId}`);
                    const deleteInput = document.getElementById(`delete_image-${questionId}`);

                    // Reset delete flag jika ada gambar baru
                    deleteInput.value = '0';

                    const file = this.files[0];
                    if (!file) return;

                    // Tampilkan preview sementara
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Hapus gambar lama jika ada
                        const existingImage = imageContainer.querySelector('.existing-image');
                        if (existingImage) {
                            existingImage.remove();
                        }

                        // Tambahkan preview sementara
                        const previewHtml = `
                <div class="position-relative d-inline-block existing-image">
                    <img src="${e.target.result}" alt="Preview Image" width="150" class="border rounded">
                    <button type="button" class="btn btn-danger btn-sm rounded-circle align-items-center justify-content-center position-absolute delete-image-btn" 
                        style="position:absolute; top:-9px; right:-8px; width:24px; height:24px; padding:0;"
                        data-question-id="${questionId}">
                                                        <i class="fas fa-times" style="font-size: 15px"></i> </button>
                    </button>
                </div>
            `;
                        imageContainer.insertAdjacentHTML('beforeend', previewHtml);

                        // Re-attach event listener untuk tombol delete
                        const newDeleteBtn = imageContainer.querySelector('.delete-image-btn');
                        newDeleteBtn.addEventListener('click', function() {
                            const questionId = this.dataset.questionId;
                            const deleteInput = document.getElementById(
                                `delete_image-${questionId}`);
                            const fileInput = document.getElementById(
                                `image-${questionId}`);

                            deleteInput.value = '1';
                            fileInput.value = '';
                            this.closest('.existing-image').remove();

                            // Auto submit form untuk menghapus gambar
                            form.dispatchEvent(new Event('submit'));
                        });

                        // Auto submit form untuk upload gambar baru
                        setTimeout(() => {
                            form.dispatchEvent(new Event('submit'));
                        }, 300);
                    };
                    reader.readAsDataURL(file);
                });
            });

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