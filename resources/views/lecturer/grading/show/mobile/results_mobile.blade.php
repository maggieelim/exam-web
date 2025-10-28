        <h5>Student Ranking & Results</h5>
        <div class="d-flex justify-content-end gap-2">
            <!-- Tombol Download -->
            <a href="{{ route('lecturer.results.download', $exam->exam_code) }}" style="height: 32px;"
                class="btn btn-warning d-flex align-items-center gap-2">
                <i class="fas fa-download"></i>
                <span>Download</span>
            </a>

            <!-- Tombol Filter -->
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px;" data-bs-toggle="collapse" data-bs-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse" title="Filter Data">
                <i class="fas fa-filter"></i>
            </button>
        </div>


        <div class="collapse card mb-3" id="filterCollapse">
            <form method="GET" action="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}">
                <div class="mx-3 py-2">
                    <div class="row g-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <div class="col-md-12">
                            <label for="name" class="form-label mb-1">Name/NIM</label>
                            <input type="text" name="name" class="form-control" placeholder="Student name / NIM"
                                value="{{ request('name') }}">
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('lecturer.results.show.' . $status, $exam->exam_code) }}"
                                class="btn btn-light btn-sm">Reset</a>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        @forelse($rankingPaginator as $result)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-auto ">
                    <div class="card-body p-3 pb-0">
                        @if ($result['rank'] == 1)
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-trophy me-1"></i>1
                            </span>
                        @elseif($result['rank'] == 2)
                            <span class="badge bg-secondary">
                                <i class="fas fa-medal me-1"></i>2
                            </span>
                        @elseif($result['rank'] == 3)
                            <span class="badge" style="background-color: orange;">
                                <i class="fas fa-medal me-1"></i>3
                            </span>
                        @else
                            <span class="badge bg-light text-dark">
                                {{ $result['rank'] }}
                            </span>
                        @endif
                        <p class="mb-1 fw-bold">Name:
                            {{ $result['student']['name'] }}
                        </p>
                        <p class="mb-1 fw-bold">NIM:
                            {{ $result['student']['student']['nim'] }} </p>
                        <div class="category-container" style="max-height: 120px; overflow-y: auto;">
                            @foreach ($result['categories_result'] as $cat)
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-light text-dark me-2"
                                        style="min-width: 120px; font-size: 0.75rem;">
                                        {{ Str::limit($cat['category_name'], 20) }}
                                    </span>
                                    <div class="progress flex-grow-1 align-items-center" style="height: 10px;">
                                        <div class="progress-bar m-0
                                            @if ($cat['percentage'] == 0) bg-secondary opacity-50
                                            @elseif($cat['percentage'] >= 80) bg-success
                                            @elseif($cat['percentage'] >= 60) bg-info
                                            @elseif($cat['percentage'] >= 40) bg-warning
                                            @else bg-danger @endif"
                                            role="progressbar" style="width: {{ max($cat['percentage'], 1) }}%"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $cat['percentage'] }}% - {{ $cat['total_correct'] }}/{{ $cat['total_question'] }} correct">
                                        </div>
                                    </div>
                                    <small class="ms-2 text-muted" style="min-width: 40px;">
                                        {{ $cat['percentage'] }}%
                                    </small>
                                </div>
                            @endforeach
                        </div>
                        <div class="my-auto pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('lecturer.feedback.' . $status, ['exam_code' => $exam->exam_code, 'nim' => $result['student_data']->nim ?? '']) }}"
                                    class="btn flex-fill bg-gradient-warning" title="Feedback">
                                    <i class="fas fa-info-circle me-1"></i> Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                    No students have attempted this exam yet.
                </td>
            </tr>
        @endforelse
        <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$rankingPaginator" />
        </div>
