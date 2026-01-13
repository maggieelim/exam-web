@props(['route', 'params' => [], 'pattern', 'icon', 'label'])

@php
$active = request()->is($pattern);
$url = isset($route) ? route($route, $params) : (isset($url) ? url($url) : '#');
@endphp

<li class="nav-item">
    <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $url }}">
        <div
            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas {{ $icon }} {{ $active ? 'text-white' : 'text-dark' }}"></i>
        </div>
        <span class="nav-link-text ms-1">{{ $label }}</span>
    </a>
</li>