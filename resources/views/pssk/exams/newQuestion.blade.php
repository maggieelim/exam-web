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
            <div class="modal-body">
                <form action="{{ route('exams.questions.updateByExcel', $exam->exam_code) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="file" class="form-label">Pilih File Excel (.xls, .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".xls,.xlsx">
                        <div class="d-flex gap-2 my-2">
                            <a href="{{ asset('templates/template_soal.xlsx') }}" class="btn px-3 btn-sm btn-info"
                                download>
                                <i class="fas fa-download"></i> Template Excel
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </div>
                </form>
                <form action="{{ route('exams.questions.updateByWord', $exam->exam_code) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Word</label>
                        <input type="file" name="word_file" class="form-control" accept=".docx">
                        <div class="d-flex gap-2 my-2">
                            <a href="{{ asset('templates/template_soal.docx') }}" class="btn px-3 btn-sm btn-info"
                                download>
                                <i class="fas fa-download"></i> Template Word
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="newQuestionModal" tabindex="-1" aria-labelledby="newQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">


            <form action="{{ route('exams.newQuestion', $exam->exam_code) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <h5 class="modal-title" id="newQuestionModalLabel">Add New Question</h5>

                    {{-- KATEGORI --}}
                    <div class="mb-2">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="category_name" class="form-control form-control-sm"
                            list="category-list" placeholder="Ketik atau pilih kategori"
                            value="{{ old('category_name', $question->category->name ?? '') }}" required>

                        <datalist id="category-list">
                            @foreach($categories as $category)
                            <option value="{{ $category->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    {{-- BADAN SOAL --}}
                    <div class="mb-2">
                        <label class="form-label">Badan Soal</label>
                        <textarea name="badan_soal" class="form-control form-control-sm" rows="1" required></textarea>
                    </div>

                    {{-- KALIMAT TANYA + IMAGE --}}
                    <div class="mb-2">
                        <label class="form-label">Kalimat Tanya</label>
                        <div class="d-flex align-items-center gap-2">
                            <textarea name="kalimat_tanya" class="form-control form-control-sm" rows="1"
                                required></textarea>

                            <label style="cursor:pointer">
                                <i class="fa-regular fa-image" style="font-size:23px;color:#5f6368"></i>
                                <input type="file" name="image" class="d-none" accept="image/*">
                            </label>
                        </div>
                    </div>

                    {{-- OPSI JAWABAN --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opsi Jawaban</label>
                        @foreach (['A','B','C','D'] as $key)
                        <div class="d-flex align-items-center mb-2">
                            <div class="form-check me-2">
                                <input type="checkbox" name="options[{{ $key }}][is_correct]" class="form-check-input">
                            </div>

                            <span class="fw-bold me-2">{{ $key }}.</span>

                            <textarea name="options[{{ $key }}][text]" class="form-control form-control-sm" rows="1"
                                required></textarea>
                        </div>
                        @endforeach
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Tambah Soal</button>
                </div>

            </form>
        </div>
    </div>
</div>