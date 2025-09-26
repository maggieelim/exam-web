@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid">
  <!-- Collapse Form -->

  {{-- Header Halaman + Tombol Upload Excel --}}
  <div class="card-header d-flex flex-row justify-content-between">
    <div>
      <h5 class="mb-0">Manage Questions - {{ $exam->title }}</h5>
    </div>
    <div class="d-flex gap-2">
      <!-- Tombol Filter -->
      <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
        <i class="fas fa-filter"></i> Filter
      </button>

      <!-- Tombol Reupload Excel (munculkan modal) -->
      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reuploadModal">
        Reupload Questions
      </button>
    </div>
  </div>

  <!-- Modal Upload Excel -->
  <div class="modal fade" id="reuploadModal" tabindex="-1" aria-labelledby="reuploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reuploadModalLabel">Reupload Questions via Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('exams.questions.updateByExcel', $exam->exam_code) }}"
          method="POST"
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
    <form method="GET" action="{{ route('exams.questions', $exam->exam_code) }}">
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
              <option value="">-- Choose Category --</option>
              @foreach($categories as $category)
              <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('exams.questions', $exam->exam_code) }}" class="btn btn-light btn-sm">Reset</a>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  @foreach($questions as $index => $question)
  <div class="card mb-4">
    <div class="card-header pb-0 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Soal {{ $index + 1 }}</h6>
      <div class="d-flex gap-3">
        <div class="d-flex flex-column text-end">
          <small class="text-muted">Exam: {{ $exam->title }}</small>
          <small class="text-muted">
            Category: {{ $question->category ? $question->category->name : 'Tidak ada kategori' }}
          </small>
        </div>
        <form action="{{ route('exams.questions.destroy', [$exam->exam_code, $question->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-lg btn-link  p-0" title="Hapus Soal">
            <i class="fas text-danger fa-times"></i>
          </button>
        </form>
      </div>
    </div>

    <div class="card-body pt-0 p-3">
      <form action="{{ route('exams.questions.update', [$exam->exam_code, $question->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-2">
          <label class="form-label">Badan Soal</label>
          <textarea name="badan_soal" class="form-control" rows="2">{{ $question->badan_soal }}</textarea>
        </div>

        <div class="mb-2">
          <label class="form-label">Kalimat Tanya</label>
          <input name="kalimat_tanya" class="form-control" rows="2" value="{{ $question->kalimat_tanya }}"></input>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Upload Gambar (Opsional)</label>
          <input type="file" name="image" id="image" class="form-control" accept="image/*">
          @if(!empty($question->image))
          <div class="mt-2 position-relative d-inline-block">
            <img src="{{ asset('storage/'.$question->image) }}" alt="Soal Image" width="300" class="border rounded">
            <!-- Tombol X -->
            <button type="button"
              class="btn btn-danger btn-sm rounded-circle align-items-center justify-content-center position-absolute "
              style="width: 30px; height: 30px; padding: 0;"
              onclick="document.getElementById('delete_image').value=1; this.closest('form').submit();">
              x
            </button>

          </div>
          @endif
        </div>
        <input type="hidden" name="delete_image" id="delete_image" value="0">

        <div class="mb-3">
          <label class="form-label">Pilihan Jawaban</label>
          <div class="row">
            @foreach($question->options as $option)
            <div class="col-md-6 mb-2">
              <div class="d-flex align-items-center">
                <div class="form-check me-2">
                  <input
                    type="checkbox"
                    name="options[{{ $option->id }}][is_correct]"
                    class="form-check-input"
                    {{ $option->is_correct ? 'checked' : '' }}>
                </div>

                <span class="me-2 fw-bold">{{ $option->option }}.</span>

                <input
                  type="text"
                  name="options[{{ $option->id }}][text]"
                  value="{{ $option->text }}"
                  class="form-control">
              </div>
            </div>
            @endforeach
          </div>
        </div>
        <button type="submit" name="action" value="update" class="btn btn-sm btn-primary">Update Soal</button>
        <button type="submit" name="action" value="anulir" class="btn btn-sm btn-warning">Anulir Soal</button>
      </form>
    </div>
  </div>
  @endforeach
  <div class="d-flex justify-content-center mt-3">
    <x-pagination :paginator="$questions" />
  </div>
</div>
@endsection