<div class="modal fade" id="newQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">



            <form method="POST" action="{{ route('exams.questions.newQuestions', $exam->exam_code) }}"
                enctype="multipart/form-data">

                @csrf {{-- ❗ POST, BUKAN PUT --}}

                <div class="modal-body">

                    {{-- BADAN SOAL --}}
                    <div class="mb-2">
                        <label class="form-label">Badan Soal</label>
                        <textarea name="badan_soal" rows="1" class="form-control auto-resize" required></textarea>
                    </div>

                    {{-- KALIMAT TANYA --}}
                    <div class="mb-2">
                        <label class="form-label">Kalimat Tanya</label>
                        <textarea name="kalimat_tanya" rows="2" class="form-control" required></textarea>
                    </div>

                    {{-- IMAGE SOAL --}}
                    <div class="mb-3">
                        <label class="form-label">Gambar Soal (Opsional)</label>
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <hr>

                    {{-- OPSI JAWABAN --}}
                    <label class="form-label fw-semibold">Opsi Jawaban</label>

                    <div class="row">
                        @foreach (['A','B','C','D'] as $i => $label)
                        <div class="col-md-6 mb-3">

                            <div class="d-flex align-items-start gap-2">
                                <div class="form-check mt-1">
                                    <input type="checkbox" name="options[{{ $i }}][is_correct]"
                                        class="form-check-input">
                                </div>

                                <span class="fw-bold mt-1">{{ $label }}.</span>

                                <textarea name="options[{{ $i }}][text]" rows="1" class="form-control auto-resize"
                                    required></textarea>
                            </div>

                            {{-- IMAGE OPTION --}}
                            <div class="d-flex justify-content-end mt-1">
                                <label class="btn btn-outline-secondary btn-sm">
                                    + Gambar
                                    <input type="file" name="options[{{ $i }}][image]" class="d-none" accept="image/*">
                                </label>
                            </div>

                        </div>
                        @endforeach
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-sm btn-primary">
                        Simpan Soal
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>