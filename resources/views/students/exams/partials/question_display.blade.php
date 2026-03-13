<div class="mt-2">
    <p class="my-0">
        {!! nl2br(e(html_entity_decode($currentQuestion->badan_soal ?? ''))) !!}
    </p>
    @if ($currentQuestion->image)
    <div class="my-2">
        <img src="{{ asset('storage/' . $currentQuestion->image) }}" alt="Gambar Soal"
            class="img-fluid rounded shadow-sm zoomable-image"
            style="max-width: 150px; max-height: 130px; cursor: zoom-in;" loading="lazy" data-bs-toggle="modal"
            data-bs-target="#imageZoomModal" data-image="{{ asset('storage/' . $currentQuestion->image) }}">
    </div>
    @endif
    <p class="my-0">
        {!! nl2br(e(html_entity_decode($currentQuestion->kalimat_tanya ?? ''))) !!}
    </p>
</div>

{{-- Image Zoom Modal --}}
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img id="zoomedImage" src="" class="img-fluid rounded shadow" style="max-height: 90vh;" loading="lazy">
            </div>
        </div>
    </div>
</div>