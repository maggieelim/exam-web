@php
$currentYear = date('Y');
$startYear = $currentYear - 7;
$endYear = $currentYear + 2;
@endphp

<select name="{{ $name }}" class="form-control" required>
    @for ($year = $startYear; $year <= $endYear; $year++)
        <option value="{{ $year }}" {{ $selected == $year ? 'selected' : '' }}>
        {{ $year }}
        </option>
        @endfor
</select>