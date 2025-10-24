@extends('layouts.user_type.auth')

@section('content')
    <div class="col-12 card mb-4 pb-0 p-3">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5>{{ $exam->title }}</h5>
            <a href="{{ route('lecturer.grade.' . $status, [
                'exam_code' => $exam->exam_code,
            ]) }}"
                class="btn btn-sm btn-secondary">
                Back
            </a>
        </div>
        <div class="row">
            <div class="col-md-4">
                <p><strong>Name:</strong> {{ $student->user->name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>NIM:</strong> {{ $student->nim }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Angkatan:</strong> {{ $student->angkatan }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Gender:</strong> {{ $student->user->gender }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Date:</strong> {{ $exam->exam_date->format('d-m-Y') }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Duration:</strong> {{ $exam->duration }} minutes</p>
            </div>
        </div>
    </div>

    <!-- Daftar Soal -->
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-3">Daftar Soal</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        </div>
    </div>

    <div class="collapse mb-3" id="filterCollapse">
        <form method="GET"
            action="{{ route('lecturer.feedback.' . $status, [
                'exam_code' => $exam->exam_code,
                'nim' => $student->nim,
            ]) }}">
            <div class="card card-body">
                <div class="row g-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="col-md-12">
                        <label for="answer_status" class="form-label mb-1">Answer Status</label>
                        <select name="answer_status" id="answer_status" class="form-control">
                            <option value="">-- All Answers --</option>
                            <option value="correct" {{ request('answer_status') == 'correct' ? 'selected' : '' }}>Correct
                            </option>
                            <option value="incorrect" {{ request('answer_status') == 'incorrect' ? 'selected' : '' }}>
                                Incorrect</option>
                            <option value="not_answered"
                                {{ request('answer_status') == 'not_answered' ? 'selected' : '' }}>Not Answered</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('lecturer.feedback.' . $status, [
                            'exam_code' => $exam->exam_code,
                            'nim' => $student->nim,
                        ]) }}"
                            class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Form Utama dengan ID untuk AJAX -->
    <form role="form" id="feedbackForm"
        action="{{ route('lecturer.feedback.update', [
            'exam_code' => $exam->exam_code,
            'nim' => $student->nim,
        ]) }}"
        method="POST">
        @csrf

        @forelse($paginatedQuestions as $question)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <p class="fw-bold mb-0">{{ $question['number'] }}. {{ $question['body'] }}</p>
                        <div class="d-flex gap-2">
                            @if ($question['is_answered'])
                                <span
                                    class="badge {{ $question['is_correct'] ? 'bg-success' : 'bg-danger' }} align-self-start">
                                    {{ $question['is_correct'] ? 'Correct' : 'Incorrect' }}
                                </span>
                            @else
                                <span class="badge bg-secondary align-self-start">Not Answered</span>
                            @endif
                            <small class="text-muted">
                                {{ $question['category'] }}
                            </small>
                        </div>
                    </div>

                    <p class="mb-2">{{ $question['question_text'] }}</p>

                    @if ($question['image'])
                        <div class="my-3">
                            <img src="{{ asset('storage/' . $question['image']) }}" alt="Gambar Soal"
                                class="img-fluid rounded shadow-sm" style="max-width: 400px;">
                        </div>
                    @endif

                    <!-- Pilihan Jawaban -->
                    @if (count($question['options']) > 0)
                        <div class="row mt-2">
                            @foreach ($question['options'] as $option)
                                <div class="col-6 mb-2">
                                    <span class="fw-bold">{{ $option['option'] }}.</span>
                                    <span>{{ $option['text'] }}</span>
                                    @if ($option['is_correct'])
                                        <span class="text-success fw-bold">✓</span>
                                    @elseif($option['is_student_answer'])
                                        <span class="text-danger fw-bold">✗</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Feedback per soal -->
                    <div class="mt-3">
                        <label class="form-label fw-bold">Feedback untuk soal ini:</label>
                        <input type="text" name="feedback[{{ $question['id'] }}]" class="form-control feedback-input"
                            value="{{ old("feedback.{$question['id']}", $question['student_feedback']) }}"
                            placeholder="Tulis feedback dosen untuk soal ini..." data-question-id="{{ $question['id'] }}">
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada soal untuk exam ini.</p>
        @endforelse

        <!-- Pagination -->
        @if ($paginatedQuestions->hasPages())
            <div class="d-flex justify-content-center mt-3">
                <x-pagination :paginator="$paginatedQuestions" />
            </div>
        @endif

        <!-- Feedback keseluruhan (sticky di bawah) -->
        <div class="card position-sticky bottom-0 bg-white p-3 border-top shadow-sm mt-4" style="z-index: 100;">
            <div class="row align-items-center">
                <!-- Label & Tanggal -->
                <div class="col-md-12 d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0">Feedback Keseluruhan Ujian:</label>
                    <small class="text-muted" id="lastSaved">
                        @if ($attempt->updated_at)
                            Terakhir disimpan: {{ $attempt->updated_at->format('d-m-Y H:i') }}
                        @else
                            Belum disimpan
                        @endif
                    </small>
                </div>

                <!-- Input & Tombol -->
                <div class="col-md-12 d-flex justify-content-between align-items-center gap-2">
                    <input type="text" name="overall_feedback" class="form-control overall-feedback"
                        placeholder="Tulis feedback untuk seluruh ujian..."
                        value="{{ old('overall_feedback', $attempt->feedback) }}">
                    <button type="submit" class="btn btn-primary w-20 m-auto" id="saveButton">
                        <i class="fas fa-save me-2"></i> Save Feedback
                    </button>
                </div>
            </div>

        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('feedbackForm');
            const saveButton = document.getElementById('saveButton');
            const saveStatus = document.getElementById('saveStatus');
            const statusText = document.getElementById('statusText');
            const lastSaved = document.getElementById('lastSaved');

            let isSaving = false;
            let autoSaveTimeout;
            let formChanged = false;

            // Fungsi untuk menampilkan status save
            function showSaveStatus(message, type = 'success') {
                const existingAlert = document.getElementById('successAlert') || document.getElementById(
                    'errorAlert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                // Buat alert baru
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} fw-bold`;
                alert.id = type === 'success' ? 'successAlert' : 'errorAlert';
                alert.style.cssText =
                    'position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;';
                alert.innerHTML = type === 'success' ?
                    `<div class="alert alert-success text-dark fw-bold" id="successAlert" style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">${message}</div>` :
                    `<div class="alert alert-danger fw-bold" id="errorAlert" style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">${message}</div>`;

                document.body.appendChild(alert);

                // Auto hide setelah beberapa detik
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, type === 'success' ? 5000 : 10000);
            }

            // Fungsi untuk menyimpan feedback via AJAX
            function saveFeedback() {
                if (isSaving) return;

                isSaving = true;
                const originalText = saveButton.innerHTML;

                // Update button state
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

                // Prepare form data
                const formData = new FormData(form);

                // Add AJAX header
                formData.append('_ajax', 'true');

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showSaveStatus('Feedback berhasil disimpan!');
                            lastSaved.textContent = `Terakhir disimpan: ${new Date().toLocaleString('id-ID')}`;
                            formChanged = false;

                            // Reset button setelah delay singkat
                            setTimeout(() => {
                                saveButton.innerHTML = originalText;
                                saveButton.disabled = false;
                            }, 1000);
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showSaveStatus('Gagal menyimpan feedback!', 'danger');
                        saveButton.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error';

                        // Reset button setelah 2 detik
                        setTimeout(() => {
                            saveButton.innerHTML = originalText;
                            saveButton.disabled = false;
                        }, 2000);
                    })
                    .finally(() => {
                        isSaving = false;
                    });
            }

            // Auto-save functionality
            function scheduleAutoSave() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    if (formChanged && !isSaving) {
                        saveFeedback();
                    }
                }, 2000); // Auto-save setelah 2 detik tidak ada perubahan
            }

            // Deteksi perubahan pada form
            const inputs = form.querySelectorAll('.feedback-input, .overall-feedback');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    formChanged = true;
                    scheduleAutoSave();
                });
            });

            // Handle form submit
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                saveFeedback();
            });

            // Konfirmasi sebelum meninggalkan halaman jika ada perubahan
            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !isSaving) {
                    e.preventDefault();
                    e.returnValue =
                        'Anda memiliki perubahan feedback yang belum disimpan. Yakin ingin meninggalkan halaman?';
                }
            });

            // Keyboard shortcut: Ctrl + S untuk save
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveFeedback();
                }
            });

            // Reset form changed flag ketika halaman dimuat
            formChanged = false;
        });
    </script>

    <style>
        #saveStatus {
            transition: all 0.3s ease;
        }

        .feedback-input:focus,
        .overall-feedback:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .card {
            transition: box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection
