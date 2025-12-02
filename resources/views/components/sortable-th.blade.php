@props(['label', 'field', 'sort', 'dir'])

@php
$nextDir = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
@endphp

<th class="text-uppercase text-dark text-sm font-weight-bolder text-wrap text-center">
    <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]) }}">
        {{ $label }}
        @if ($sort === $field)
        <i class="fa fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}"></i>
        @endif
    </a>
</th>