<div class="d-flex justify-content-end gap-2">
    <!-- Tombol Download -->
    <a href="{{ route('lecturer.results.downloadQuestions', $exam->exam_code) }}" style="height: 32px;"
        class="btn btn-warning d-flex align-items-center">
        <i class="fas fa-download"></i>
    </a>

    <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
        style="width: 32px; height: 32px;" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
        aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
        <i class="fas fa-filter"></i>
    </button>
</div>

<div class="collapse card mb-3" id="filterCollapse">
    <form method="GET" action="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}">
        <div class="card-body">
            <div class="row g-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="tab" value="answers">
                <div class="col-12">
                    <label for="difficulty_level" class="form-label mb-1">Question Difficulty</label>
                    <select name="difficulty_level" id="difficulty_level" class="form-control form-control-sm">
                        <option value="">-- All Levels --</option>
                        @foreach ($difficultyLevel as $level)
                        <option value="{{ $level }}" {{ request('difficulty_level')==$level ? 'selected' : '' }}>
                            {{ $level }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}?tab=answers"
                        class="btn btn-light btn-sm">
                        Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="row">
    @foreach ($questionAnalysisPaginator as $index => $analysis)
    @php
    $isAnulir = $analysis->question->is_anulir ?? false;
    @endphp
    <div class="col-12 col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <!-- Stats Header - Stacked untuk Mobile -->
                <div class="mb-3">

                    <!-- Nomor Soal -->
                    <div class="fw-bold text-primary">
                        No. {{ $questionNumberMap[$analysis->question->kode_soal] ?? '-' }}
                    </div>

                    <!-- Correct -->
                    <div class="d-flex gap-2 align-items-center mb-1">
                        <span class="fw-bold=">Correct: </span>
                        @if ($isAnulir)
                        <span class="badge bg-secondary">-</span>
                        @else
                        <span class="badge ms-2 
            {{ $analysis['correct_percentage'] >= 75
                ? 'bg-gradient-success'
                : ($analysis['correct_percentage'] >= 60
                    ? 'bg-gradient-info'
                    : ($analysis['correct_percentage'] >= 20
                        ? 'bg-gradient-warning'
                        : 'bg-gradient-danger')) }}">
                            {{ $analysis['correct_percentage'] }}%
                        </span>
                        @endif
                    </div>

                    <!-- Discrimination -->
                    <div class="d-flex gap-2 align-items-center mb-1">
                        <span class="fw-bold">Discrimination:</span>
                        @if ($isAnulir)
                        <span class="badge bg-secondary">-</span>
                        @else
                        @php
                        $d = $analysis->discrimination_index;

                        $class = match (true) {
                        $d >= 0.4 => 'bg-gradient-success',
                        $d >= 0.3 => 'bg-gradient-info',
                        $d >= 0.2 => 'bg-gradient-warning',
                        $d >= 0.01 => 'bg-gradient-secondary',
                        default => 'bg-gradient-danger',
                        };
                        @endphp

                        <span class="badge {{ $class }} text-white">
                            {{ $analysis->discrimination_index }}
                        </span>
                        @endif
                    </div>

                    <!-- Difficulty -->
                    <div class="d-flex gap-2 align-items-center">
                        <span class="text-muted">Difficulty:</span>
                        @if ($isAnulir)
                        <span class="badge bg-secondary">-</span>
                        @else
                        <span class="badge 
                                    {{ $analysis['difficulty_level'] == 'Easy'
                                        ? 'bg-gradient-success'
                                        : ($analysis['difficulty_level'] == 'Medium'
                                            ? 'bg-gradient-info'
                                            : ($analysis['difficulty_level'] == 'Fair'
                                                ? 'bg-gradient-warning'
                                                : 'bg-gradient-danger')) }}">
                            {{ $analysis['difficulty_level'] }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Question Content -->
                <div class="mb-2">
                    <p class="question-text mb-1">
                        {{ $analysis->question->badan_soal ?: $analysis->question->kalimat_tanya }}
                    </p>
                </div>

                <!-- Action -->
                <div class="d-flex">
                    @if ($isAnulir)
                    <button type="button" class="mb-0 btn btn-sm btn-success w-100 ">Dianulir</button>
                    @else
                    <form class="question-form w-100" data-question-id="{{ $analysis->question->id }}">
                        @csrf
                        @method('PUT')

                        <button type="button" class="mb-0 anulir-btn btn btn-sm btn-warning w-100 "
                            data-question-id="{{ $analysis->question->id }}"
                            title="Anulir soal - semua jawaban dianggap benar">
                            Anulir
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$questionAnalysisPaginator" />
</div>

<style>
    .question-text {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        /* jumlah baris */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
        min-height: 2.8em;
    }
</style>

<script>
    // ==== Event Delegation untuk tombol Anulir ====
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.anulir-btn');
        if (!target) return;

        const questionId = target.dataset.questionId;
        const confirmText =
            'Yakin ingin menganulir soal ini? Semua jawaban siswa akan dianggap benar. Tindakan ini tidak dapat dibatalkan.';

        if (!confirm(confirmText)) return;

        handleAnulirAction(questionId, target);
    });

    // ==== Proses utama Anulir ====
    function handleAnulirAction(questionId, button) {
        const form = document.querySelector(`.question-form[data-question-id="${questionId}"]`);
        const formData = new FormData(form);
        formData.append('action', 'anulir');

        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Anulir...`;

        fetch(`/pssk/exams/{{ $exam->exam_code }}/questions/${questionId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Gagal memproses permintaan.', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
    }
</script>