{{-- resources/views/components/score-picker.blade.php --}}
<div>
    <h6 style="font-size: clamp(0.75rem, 2.3vw, 1rem);">
        {{ $label }}</h6>
    <input type="hidden" name="{{ $name }}" id="input-{{ $name }}" value="{{ $value }}">

    <div class="d-flex col-12 col-md-8 gap-2">
        @for ($i = 0; $i <= 3; $i++) <button type="button"
            class="btn flex-fill score-btn {{ $value == $i ? 'btn-info' : 'btn-outline-secondary' }}"
            data-target="input-{{ $name }}" data-value="{{ $i }}">
            {{ $i }}
            </button>
            @endfor
    </div>
</div>