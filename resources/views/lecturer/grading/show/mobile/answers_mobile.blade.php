<div class="d-flex justify-content-end gap-2">
    <!-- Tombol Download -->
    <a href="{{ route('lecturer.results.downloadQuestions', $exam->exam_code) }}" style="height: 32px;"
        class="btn btn-warning d-flex align-items-center gap-2">
        <i class="fas fa-download"></i>
        <span>Download</span>
    </a>

    <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
        style="width: 32px; height: 32px;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false"
        aria-controls="filterCollapse" title="Filter Data">
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

<div class="row">
    @forelse($questionAnalysisPaginator as $analysis)
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <!-- Stats Header - Stacked untuk Mobile -->
                    <div class="d-flex flex-column gap-2 mb-3">
                        <!-- Correct -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small">Correct:</span>
                            <div class="d-flex align-items-center gap-1">
                                <span
                                    class="small">{{ $analysis['correct_count'] }}/{{ $analysis['total_students'] }}</span>
                                <span
                                    class="badge 
                                    {{ $analysis['correct_percentage'] >= 80
                                        ? 'bg-gradient-success'
                                        : ($analysis['correct_percentage'] >= 60
                                            ? 'bg-gradient-info'
                                            : ($analysis['correct_percentage'] >= 40
                                                ? 'bg-gradient-warning'
                                                : 'bg-gradient-danger')) }}"
                                    style="font-size: 0.7rem;">
                                    {{ $analysis['correct_percentage'] }}%
                                </span>
                            </div>
                        </div>

                        <!-- Discrimination Index -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small">Discrimination:</span>
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
                                                : 'bg-gradient-danger'))) }}"
                                style="font-size: 0.7rem;">
                                {{ $analysis['discrimination_index'] }}
                            </span>
                        </div>

                        <!-- Difficulty -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small">Difficulty:</span>
                            <span
                                class="badge 
                                {{ $analysis['difficulty_level'] == 'Easy'
                                    ? 'bg-gradient-success'
                                    : ($analysis['difficulty_level'] == 'Medium'
                                        ? 'bg-gradient-info'
                                        : ($analysis['difficulty_level'] == 'Fair'
                                            ? 'bg-gradient-warning'
                                            : 'bg-gradient-danger')) }}"
                                style="font-size: 0.7rem;">
                                {{ $analysis['difficulty_level'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Question Content -->
                    <div class="mb-3">
                        @if (!empty($analysis['question_text']))
                            <p class="fw-bold mb-1 question-text">
                                {{ $analysis['question_text'] }}
                            </p>
                        @endif
                        @if (!empty($analysis['question']))
                            <p class="text-muted mb-0 small question-text">
                                {{ $analysis['question'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Question Image -->
                    @if ($analysis['image'])
                        <div class="my-3 text-center">
                            <img src="{{ asset('storage/' . $analysis['image']) }}" alt="Gambar Soal"
                                class="img-fluid rounded shadow-sm w-100"
                                style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif

                    <!-- Options Analysis -->
                    @if (!empty($optionsAnalysis[$analysis['question_id']]))
                        <div class="mt-3">
                            <h6 class="small fw-bold mb-2">Answer Options:</h6>
                            <div class="options-container">
                                @foreach ($optionsAnalysis[$analysis['question_id']] as $optionIndex => $option)
                                    <div class="option-item mb-2 p-2 rounded border">
                                        <!-- Option Header -->
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold me-2 option-letter">
                                                    {{ chr(65 + $optionIndex) }}.
                                                </span>
                                                @if (!empty($option['is_correct']))
                                                    <span class="badge bg-success text-white px-2 py-1 me-2"
                                                        style="font-size: 0.65rem;">
                                                        Correct Answer
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-light text-dark px-2 py-1"
                                                    style="font-size: 0.7rem;">
                                                    {{ $option['percentage'] ?? 0 }}%
                                                </span>
                                                <span class="text-muted small" style="font-size: 0.7rem;">
                                                    ({{ $option['count'] ?? 0 }})
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Option Text -->
                                        <div class="option-text-container">
                                            <div class="option-text">
                                                {{ $option['option_text'] ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning py-2 small mb-0">
                            Tidak ada data analisis untuk soal ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-4">
                <i class="fas fa-info-circle me-2"></i>Tidak ada data analisis yang tersedia
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$questionAnalysisPaginator" />
</div>

<style>
    .question-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.4;
    }

    .option-item {
        transition: background-color 0.2s ease;
    }

    .option-item:hover {
        background-color: #f0f0f0;
    }

    .option-letter {
        font-size: 0.9rem;
        min-width: 20px;
    }

    .option-text-container {
        margin-top: 0.5rem;
    }

    .option-text {
        font-size: 0.85rem;
        line-height: 1.4;
        word-wrap: break-word;
        overflow-wrap: break-word;
        color: #333;
        padding: 0.5rem;
        background: white;
        border-radius: 4px;
        border-left: 3px solid #cb0c9f;
    }

    .options-container {
        max-height: 400px;
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .option-item {
            padding: 0.75rem;
        }

        .option-text {
            font-size: 0.8rem;
            padding: 0.4rem;
        }

        .options-container {
            max-height: 300px;
        }
    }

    /* Scrollbar styling untuk options container */
    .options-container::-webkit-scrollbar {
        width: 6px;
    }

    .options-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .options-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .options-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
