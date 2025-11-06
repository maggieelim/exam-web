<div class="mt-4">
    <div class="d-flex flex-row justify-content-end  gap-2">
        {{-- <a href="{{ route('exams.questions.previous', $exam->exam_code) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-file me-2"></i>
            Manage Questions
        </a> --}}
        <a href="{{ route('lecturer.results.downloadQuestions', $exam->exam_code) }}" class="btn btn-sm btn-info">
            <i class="fas fa-download me-2"></i>
            Download
        </a>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
            data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>

    <div class="collapse card mb-3" id="filterCollapse">
        <form method="GET" action="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}">
            <div class="mx-3 mb-2 pb-2">
                <div class="row g-2">
                    <input type="hidden" name="status" value="{{ $status }}" class="m-0 p-0">
                    <input type="hidden" name="tab" value="answers" class="m-0 p-0">
                    <div class="col-md-12">
                        <label for="difficulty_level" class="form-label mb-1">Question Difficulty</label>
                        <select name="difficulty_level" id="difficulty_level" class="form-control">
                            <option value="">-- All Levels --</option>
                            @foreach ($difficultyLevel as $level)
                                <option value="{{ $level }}"
                                    {{ request('difficulty_level') == $level ? 'selected' : '' }}>
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
</div>

<div class="card px-0 pt-0 pb-2">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center w-auto text-uppercase text-dark text-sm font-weight-bolder">No
                    </th>
                    <th rowspan="2" class="text-center text-uppercase text-dark text-sm font-weight-bolder">Question
                    </th>
                    <th rowspan="2" class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                        Discrimination<br>Index
                    </th>
                    <th rowspan="2" class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                        Correct
                    </th>
                    <th rowspan="2" class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                        Difficulty
                    </th>
                    <th rowspan="2" class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                        Anulir
                    </th>
                    {{-- <th colspan="4" class="text-center text-uppercase text-dark text-sm font-weight-bolder">Option
                    </th> --}}
                </tr>
                {{-- <tr>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">A</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">B</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">C</th>
                    <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">D</th>
                </tr> --}}
            </thead>

            <tbody>
                @foreach ($questionAnalysisPaginator as $index => $analysis)
                    @php
                        $isAnulir = $analysis['is_anulir'] ?? false;
                    @endphp
                    <tr>
                        <td class="align-middle text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="align-middle truncate-text">
                            {{ $analysis['question_text'] }}
                        </td>

                        {{-- Discrimination Index --}}
                        <td class="align-middle text-center">
                            @if ($isAnulir)
                                <span class="badge bg-secondary">-</span>
                            @else
                                <span
                                    class="badge 
                                    {{ $analysis['discrimination_index'] > 0.4
                                        ? 'bg-gradient-success'
                                        : ($analysis['discrimination_index'] >= 0.3
                                            ? 'bg-gradient-info'
                                            : ($analysis['discrimination_index'] >= 0.2
                                                ? 'bg-gradient-warning'
                                                : ($analysis['discrimination_index'] >= 0.1
                                                    ? 'bg-gradient-orange'
                                                    : 'bg-gradient-danger'))) }}">
                                    {{ $analysis['discrimination_index'] }}
                                </span>
                            @endif
                        </td>

                        {{-- Difficulty --}}
                        <td class="align-middle text-center">
                            @if ($isAnulir)
                                <span class="badge bg-secondary">-</span>
                            @else
                                <span
                                    class="badge ms-2 
            {{ $analysis['correct_percentage'] >= 80
                ? 'bg-gradient-success'
                : ($analysis['correct_percentage'] >= 60
                    ? 'bg-gradient-info'
                    : ($analysis['correct_percentage'] >= 40
                        ? 'bg-gradient-warning'
                        : 'bg-gradient-danger')) }}">
                                    {{ $analysis['correct_percentage'] }}%
                                </span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            @if ($isAnulir)
                                <span class="badge bg-secondary">-</span>
                            @else
                                <span
                                    class="badge 
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
                        </td>

                        {{-- Anulir Button --}}
                        <td class="align-middle text-center">
                            @if ($isAnulir)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Dianulir
                                </span>
                            @else
                                <form class="question-form d-inline" data-question-id="{{ $analysis['question_id'] }}">
                                    @csrf
                                    @method('PUT')
                                    <a class="btn btn-sm btn-warning anulir-btn"
                                        data-question-id="{{ $analysis['question_id'] }}"
                                        title="Anulir soal - semua jawaban dianggap benar">
                                        <i class="fas fa-ban me-1"></i> Anulir
                                    </a>
                                </form>
                            @endif
                        </td>

                        {{-- Options A-D --}}
                        {{-- @php
                            $options = $optionsAnalysis[$analysis['question_id']] ?? [];
                        @endphp

                        @foreach (['A', 'B', 'C', 'D'] as $optIndex => $optLabel)
                            @php
                                $option = $options[$optIndex] ?? null;
                            @endphp
                            <td class="align-middle text-center">
                                @if ($option)
                                    <div class="d-flex gap-1 align-items-center">
                                        <span
                                            class="fw-semibold {{ $isAnulir ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $option['percentage'] ?? 0 }}%
                                        </span>
                                        <small class="text-muted">({{ $option['count'] ?? 0 }})</small>
                                        @if (!empty($option['is_correct']) && !$isAnulir)
                                            <span class="text-success"><i class="fas fa-check"></i></span>
                                        @endif
                                        @if ($isAnulir)
                                            <span class="text-success small"><i class="fas fa-check-double"></i></span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$questionAnalysisPaginator" />
        </div>
    </div>
</div>

<style>
    .truncate-text {
        max-width: 350px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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

        fetch(`/exams/{{ $exam->exam_code }}/questions/${questionId}`, {
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

    // ==== Efek hover tombol ====
    document.addEventListener('mouseenter', e => {
        if (e.target.matches('.anulir-btn')) {
            e.target.style.transform = 'scale(1.05)';
            e.target.style.transition = 'transform 0.2s ease';
        }
    }, true);

    document.addEventListener('mouseleave', e => {
        if (e.target.matches('.anulir-btn')) {
            e.target.style.transform = 'scale(1)';
        }
    }, true);
</script>
