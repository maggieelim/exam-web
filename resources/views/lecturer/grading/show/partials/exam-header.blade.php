<!-- Exam Info -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="fas fa-chart-bar me-2"></i>
          General Exam Statistics ({{ $exam->title }} Blok {{ $exam->course->name }})
        </h5>
        <div class="d-flex gap-3 align-items-center">
          <!-- Publication Status -->
          <span class="badge {{ $exam->is_published ? 'bg-success' : 'bg-danger' }}">
            {{ $exam->is_published ? 'Published' : 'Unpublished' }}
          </span>

          <!-- Filter Toggle -->
          <button class="btn btn-sm btn-outline-primary"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#chartCollapse"
            aria-expanded="false"
            aria-controls="chartCollapse">
            <i class="fas fa-filter me-1"></i> Charts
          </button>

          <!-- Back Button -->
          <a href="{{ route('lecturer.results.index', $status) }}"
            class="btn btn-sm btn-outline-secondary">
            Back
          </a>
        </div>
      </div>
    </div>
  </div>
</div>