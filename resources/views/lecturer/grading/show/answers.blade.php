@foreach($exam->questions as $question)
<div class="card mb-4">
  <div class="card-body">
    <p class="fw-bold m-0">{{ $loop->iteration }}. {{ $question->badan_soal }}</p>
    <p class="text-muted m-0">{{ $question->kalimat_tanya }}</p>

    @if(!empty($optionsAnalysis[$question->id]))
    <div class="row mt-3">
      @foreach($optionsAnalysis[$question->id] as $optionIndex => $option)
      <div class="col-12 d-flex align-items-center mb-2">
        <!-- Huruf a, b, c -->
        <div class="fw-bold me-2">
          {{ chr(97 + $optionIndex) }}.
        </div>

        <!-- Teks opsi -->
        <div class="me-2">
          {{ $option['option_text'] ?? '' }}
        </div>

        <!-- Tanda benar -->
        @if(!empty($option['is_correct']))
        <div class="text-success me-2">✔</div>
        @endif

        <!-- Jumlah siswa -->
        <div class="me-2 text-muted">
          ({{ $option['count'] ?? 0 }} siswa)
        </div>

        <!-- Persentase -->
        <div class="badge bg-light text-dark">
          {{ $option['percentage'] ?? 0 }}%
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="alert alert-warning m-2">
      Tidak ada data analisis untuk soal ini.
    </div>
    @endif
  </div>
</div>
@endforeach